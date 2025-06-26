<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\AssetExport;
use App\Modules\Admin\Exports\DeveloperAssetExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Helpers\UpdatePaymentsHelper;
use App\Modules\Asset\Helpers\UpdateRenovationPaymentsHelper;
use App\Modules\Asset\Helpers\UpdateRentalPaymentsHelper;
use App\Modules\Asset\Http\Requests\AssetRequest;
use App\Modules\Asset\Http\Requests\AssetSaleRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\AssetAgreement;
use App\Modules\Asset\Models\AssetAttachment;
use App\Modules\Asset\Models\AssetGallery;
use App\Modules\Asset\Models\AssetInformation;
use App\Modules\Asset\Models\CurrentValue;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\PaymentsHistory;
use App\Modules\Asset\Models\RenovationPayment;
use App\Modules\Asset\Models\RenovationPaymentsHistory;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
use App\Modules\Asset\Services\AssetCompareService;
use App\Utilities\ServiceResponse;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class AssetController extends BaseController
{

    protected $baseModuleName = 'asset::';

    /**
     * @var string
     */
    public $moduleTitle = 'asset';

    /**
     * @var string
     */
    public $viewFolderName = 'asset';
    public $baseName = 'asset.';
    /**
     * @var UpdatePaymentsHelper
     */
    protected $updatePaymentsHelper;
    /**
     * @var UpdateRentalPaymentsHelper
     */
    protected $updateRentalPaymentsHelper;
    /**
     * @var AssetCompareService
     */
    protected $assetCompareService;
    private UpdateRenovationPaymentsHelper $updateRenovationPaymentsHelper;

    /**
     * @param UpdatePaymentsHelper $updatePaymentsHelper
     * @param UpdateRentalPaymentsHelper $updateRentalPaymentsHelper
     * @param UpdateRenovationPaymentsHelper $updateRenovationPaymentsHelper
     * @param AssetCompareService $assetCompareService
     */
    public function __construct(
        UpdatePaymentsHelper           $updatePaymentsHelper,
        UpdateRentalPaymentsHelper     $updateRentalPaymentsHelper,
        UpdateRenovationPaymentsHelper $updateRenovationPaymentsHelper,
        AssetCompareService            $assetCompareService
    )
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->updatePaymentsHelper = $updatePaymentsHelper;
        $this->updateRentalPaymentsHelper = $updateRentalPaymentsHelper;
        $this->assetCompareService = $assetCompareService;
        $this->updateRenovationPaymentsHelper = $updateRenovationPaymentsHelper;
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'Sales manager', 'SalesManager'];

        $query = Asset::query();
//        $query->where('sale_status', 'active');

        $statusFilter = $request->status ?? 'active';

        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', '=', $userId);
        }
        
        // Handle status filtering based on status parameter
        if ($statusFilter === 'active') {
            $query->where('is_archived', false);
            $query->where('sale_status', 'active');
        } elseif ($statusFilter === 'archived') {
            $query->where('is_archived', true);
        } elseif ($statusFilter === 'sold') {
            $query->where('sale_status', 'sold');
        }
        // 'all' status will not apply any filters

        // Apply filters if provided in the request
        if ($request->investor && $request->investor != 'all') {
            $investorNamesArray = explode(' ', $request->investor);

            // The first part is the name
            $firstName = array_shift($investorNamesArray);

            // The remaining parts are the surname
            $surname = implode(' ', $investorNamesArray);

//            $investorNamesArray = explode(' ', $request->investor);
            $investorUser = Investor::where('name', $firstName)
                ->where('surname', $surname)->first();

            if (isset($investorUser->id)) {
                $query->whereHas('investors', function ($q) use ($investorUser) {
                    $q->where('id', $investorUser->id);
                });
            }
        }

        if ($request->manager && $request->manager != 'all') {
            $managerNamesArray = explode(' ', $request->manager);

            $managerFirstName = array_shift($managerNamesArray);

            $managerSurname = implode(' ', $managerNamesArray);

            $managerUser = Admin::where('name', $managerFirstName)
                ->where('surname', $managerSurname)->
                first();
            $investorIds = $managerUser->investors->pluck('id')->toArray();

            if (!empty($investorIds)) {
                $query->whereHas('investors', function ($q) use ($investorIds) {
                    $q->whereIn('investors.id', $investorIds);
                });
            }
        }

        if ($request->asset && $request->asset != 'all') {
            $query->where('project_name', 'like', '%' . $request->asset . '%');
        }

        if ($request->asset_type && $request->asset_type != 'all') {
            $query->where('type', $request->asset_type);
        }

        if ($request->asset_status && $request->asset_status != 'all') {
            $query->where('asset_status', $request->asset_status);
        }

        if ($request->agreement_status && $request->agreement_status != 'all') {
            $query->where('agreement_status', $request->agreement_status);
        }


        if ($request->agreement_date && $request->agreement_date !== 'null') {
            $createdDates = explode(',', $request->agreement_date);

            if (isset($createdDates[0])) {
                $query->where('agreement_date', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $query->where('agreement_date', '<=', $createdDates[1]);
            }
        }

        if ($request->payment_date && $request->payment_date !== 'null') {
            $dates = explode(',', $request->payment_date);
            $start = $dates[0] ?? null;
            $end = $dates[1] ?? null;
            $query->where(function ($query) use ($start, $end) {
                $query->where('created_at', '>=', $start)
                    ->orWhereHas('rentals', function ($q) use ($start, $end) {
                        $q->where('status', 0)
                            ->where('payment_date', '>=', $start)
                            ->where('payment_date', '<=', $end);
                    })
                    ->orWhereHas('payments', function ($q) use ($start, $end) {
                        $q->where('status', 0)
                            ->where('payment_date', '>=', $start)
                            ->where('payment_date', '<=', $end);
                    })
                    ->orWhereHas('renovationPayments', function ($q) use ($start, $end) {
                        $q->where('status', 0)
                            ->where('payment_date', '>=', $start)
                            ->where('payment_date', '<=', $end);
                    });
//                    ->orWhereHas('investments', function ($q) use ($start, $end) {
//                        $q->where('date', '>=', $start);
//                        $q->where('date', '<=', $end);
//                    });
            });
        }

        // Order by descending asset ID
        $this->baseData['allData'] = $query->orderByDesc('id')->paginate(25);

        // Return view with filtered data
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }


    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function myassets(Request $request)
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        $statusFilter = $request->status ?? 'active';
        $isDeveloper = Auth::guard('developer')->check();
        // Check if the user is a developer
        if ($isDeveloper) {
            $developer = Auth::guard('developer')->user();
            // For developers, find assets with matching names
            $userAssets = Asset::whereIn('project_name', $developer->assets()->pluck('asset_name')->toArray())->where('developer_access', 1)
                ->orderByDesc('id');
        } else {
            // For investors, use the relationship
            $userAssets = $user->assets()->where('status', 'completed')->orderByDesc('id');
        }
        
        // Handle status filtering based on status parameter
        if ($statusFilter === 'active') {
            $userAssets->where('is_archived', false);
            $userAssets->where('sale_status', 'active');
        } elseif ($statusFilter === 'archived') {
            $userAssets->where('is_archived', true);
        } elseif ($statusFilter === 'sold') {
            $userAssets->where('sale_status', 'sold');
        }
        // 'all' status will not apply any filters

        if ($request->agreement_date && $request->agreement_date !== 'null') {
            $createdDates = explode(',', $request->agreement_date);

            if (isset($createdDates[0])) {
                $userAssets->where('agreement_date', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $userAssets->where('agreement_date', '<=', $createdDates[1]);
            }
        }

        if ($request->agreement_status && $request->agreement_status != 'all') {
            $userAssets->where('agreement_status', $request->agreement_status);
        }

        if ($request->city && $request->city != 'all') {
            $userAssets->where('city', $request->city);
        }

        if ($request->asset && $request->asset != 'all') {
            $userAssets->where('project_name', 'like', '%' . $request->asset . '%');
        }

        if ($request->asset_type && $request->asset_type != 'all') {
            $userAssets->where('type', $request->asset_type);
        }

        if ($request->payment_date && $request->payment_date !== 'null') {
            $dates = explode(',', $request->payment_date);
            $start = $dates[0] ?? null;
            $end = $dates[1] ?? null;
            $userAssets->where(function ($userAssets) use ($start, $end, $isDeveloper) {
                if (!$isDeveloper) {
                    $userAssets->where('created_at', '>=', $start)
                        ->orWhereHas('rentals', function ($q) use ($start, $end) {
                            $q->where('status', 0)
                                ->where('payment_date', '>=', $start)
                                ->where('payment_date', '<=', $end);
                        });
                    $userAssets->orWhereHas('renovationPayments', function ($q) use ($start, $end) {
                        $q->where('status', 0)
                            ->where('payment_date', '>=', $start)
                            ->where('payment_date', '<=', $end);
                    });
                }
                $userAssets->orWhereHas('payments', function ($q) use ($start, $end) {
                    $q->where('status', 0)
                        ->where('payment_date', '>=', $start)
                        ->where('payment_date', '<=', $end);
                });

//                    ->orWhereHas('investments', function ($q) use ($start, $end) {
//                        $q->where('date', '>=', $start);
//                        $q->where('date', '<=', $end);
//                    });
            });
        }

        if ($request->investor && $request->investor != 'all') {
            $investorNamesArray = explode(' ', $request->investor);

            // The first part is the name
            $firstName = array_shift($investorNamesArray);

            // The remaining parts are the surname
            $surname = implode(' ', $investorNamesArray);

//            $investorNamesArray = explode(' ', $request->investor);
            $investorUser = Investor::where('name', $firstName)
                ->where('surname', $surname)->first();

            if (isset($investorUser->id)) {
                $userAssets->whereHas('investors', function ($q) use ($investorUser) {
                    $q->where('id', $investorUser->id);
                });
            }
        }

        if ($request->manager && $request->manager != 'all') {
            $managerNamesArray = explode(' ', $request->manager);

            $managerFirstName = array_shift($managerNamesArray);

            $managerSurname = implode(' ', $managerNamesArray);

            $managerUser = Admin::where('name', $managerFirstName)
                ->where('surname', $managerSurname)->
                first();
            $investorIds = $managerUser->investors->pluck('id')->toArray();

            if (!empty($investorIds)) {
                $userAssets->whereHas('investors', function ($q) use ($investorIds) {
                    $q->whereIn('investors.id', $investorIds);
                });
            }
        }


        if ($userAssets->count() > 0) {
            $this->baseData['allData'] = $userAssets->paginate(25);
        } else {
            if (Auth::guard('developer')->check()) {
                $developer = Auth::guard('developer')->user();
                $this->baseData['allData'] = Asset::whereIn('project_name', $developer->assets()->pluck('asset_name')->toArray())->where('developer_access', 1)
                    ->where('sale_status', 'sold')
                    ->orderByDesc('id')->paginate(25);
            }
            if (Auth::guard('investor')->check()) {
                $this->baseData['allData'] = $user->assets()->where('sale_status', 'sold')->orderByDesc('id')->paginate(25);
            }
        }

        // Calculate the "Paid" amount for each asset
        $this->calculatePaidAmounts($this->baseData['allData'], $request);

        if (Auth::guard('developer')->check()) {

            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index_developer', $this->baseData);
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', $this->baseData);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createData(Request $request)
    {
        try {
            $this->baseData['routes'] = [
                'create' => route($this->baseName . 'create'),
                'create_data' => route($this->baseName . 'create_data'),
                'save' => route($this->baseName . 'store'),
                'edit' => route($this->baseName . 'edit', []),
            ];

            if ($request->get('id')) {
                $asset = Asset::findOrFail($request->get('id'));

                $salesManager = null;

                if ($asset->investors->isNotEmpty()) {
                    $investor = $asset->investors->first();
                    $salesManager = Admin::find($investor->admin_id);
                }

                $this->baseData['item'] = $asset;
                $investors = $asset->investors;
                $investorNames = [];
                foreach ($investors as $investor) {
                    $investorNames[] = $investor->name . ' ' . $investor->surname;
                }

                $investorNames = implode(' / ', $investorNames);
                $this->baseData['item']['investor_ids'] = $investors->pluck('id')->toArray();
                $this->baseData['item']['investorNames'] = $investorNames;
                $this->baseData['item']['attachments'] = AssetAttachment::where('asset_id', $asset->id)->get();
                $this->baseData['item']['extraDetails'] = AssetInformation::where('asset_id', $asset->id)->get();
                $this->baseData['item']['agreements'] = AssetAgreement::where('asset_id', $asset->id)->get();
                $this->baseData['item']['gallery'] = AssetGallery::where('asset_id', $asset->id)->get();
                $this->baseData['item']['payments'] = Payment::where('asset_id', $asset->id)->get();
                $this->baseData['item']['payments_histories'] = PaymentsHistory::where('asset_id', $asset->id)->get();
                $this->baseData['item']['renovation_payments'] = RenovationPayment::where('asset_id', $asset->id)->get();
                $this->baseData['item']['renovation_payments_histories'] = RenovationPaymentsHistory::where('asset_id', $asset->id)->get();
                $tenant = Tenant::where('asset_id', $asset->id)->where('status', true)->orderByDesc('id')->first();

                $this->baseData['item']['tenant'] = $this->baseData['item']['rental_payments_histories'] = [];
                if ($tenant) {
                    $this->baseData['item']['tenant'] = $tenant;
                    $this->baseData['item']['rental_payments_histories'] = RentalPaymentsHistory::where('asset_id', $asset->id)->where('tenant_id', $tenant->id)->where('status', 1)->get();
                }
                $this->baseData['item']['rentals'] = Rental::where('asset_id', $asset->id)->get();
                $this->baseData['item']['needToCompleteRent'] = !Rental::where('asset_id', $asset->id)->where('status', 0)->count() && $asset->asset_status === 'Rented';
                $this->baseData['item']['currentValues'] = CurrentValue::where('asset_id', $asset->id)->orderByDesc('id')->get();
                $this->baseData['salesManager'] = $salesManager;

                $files = [];
                if ($asset->attachments) {
                    foreach ($asset->attachments as $item) {
                        $files[] = [
                            'name' => $item->name,
                            'image' => $item->image,
                            'type' => substr($item->type, 0, 5) == 'image' ? 'image' : null
                        ];
                    }
                }
                $this->baseData['item']['files'] = $files;
            }
            $this->baseData['item']['countries'] = Country::get('country');
            $this->baseData['item']['prefixes'] = Country::groupBy('prefix')->get('prefix');
            if (\Auth::guard('admin')->check()) {
                if (auth()->user()->getRolesNameAttribute() == 'administrator') {
                    $this->baseData['investors'] = Investor::get(['name', 'surname', 'id']);
                } else {
                    $this->baseData['investors'] = Investor::where('admin_id', auth()->user()->getAuthIdentifier())->get(['name', 'surname', 'id']);
                }
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param AssetRequest $request
     * @return JsonResponse
     */
    public function store(AssetRequest $request)
    {
        $path = $floorPlanPath = $flatPlanPath = $agreementPath = $ownershipCertificatePath = $renovationAgreementPath = null;

        if (isset($request->id)) {
            $asset = Asset::where('id', $request->id)->first();
            $adminId = Auth::user()->getAuthIdentifier();

            $originalData = $asset ? $asset->getOriginal() : null;
            $originalAttachments = $asset ? $asset->attachments()->get()->toArray() : [];
            $originalInformations = $asset ? $asset->informations()->get()->toArray() : [];
            $originalAgreements = $asset ? $asset->agreements()->get()->toArray() : [];
            $originalGallery = $asset ? $asset->gallery()->get()->toArray() : [];
            $originalRentals = $asset ? $asset->rentals()->get()->toArray() : [];
            $originalPayments = $asset ? $asset->payments()->get()->toArray() : [];


            if ($request->hasFile('icon')) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }

                $file = $request->file('icon');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            } else if ($request->input('icon') === null) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }
                $path = null;
            } else {
                $path = $request->icon;
            }

            if ($request->hasFile('floor_plan')) {
                if ($asset->floor_plan && Storage::disk('public')->exists($asset->floor_plan)) {
                    Storage::disk('public')->delete($asset->floor_plan);
                }

                $floorPlanFile = $request->file('floor_plan');
                $originalFileName = time() . '_' . $floorPlanFile->getClientOriginalName();
                $floorPlanPath = $floorPlanFile->storeAs('uploads', $originalFileName, 'public');
                $floorPlanPath = Storage::url($floorPlanPath);
            } else if ($request->input('flat_plan') === null) {
                if ($asset->flat_plan && Storage::disk('public')->exists($asset->flat_plan)) {
                    Storage::disk('public')->delete($asset->flat_plan);
                }
                $floorPlanPath = null;
            } else {
                $floorPlanPath = $request->floor_plan;
            }

            if ($request->hasFile('flat_plan')) {
                if ($asset->flat_plan && Storage::disk('public')->exists($asset->flat_plan)) {
                    Storage::disk('public')->delete($asset->flat_plan);
                }

                $flatPlanFile = $request->file('flat_plan');
                $originalFileName = time() . '_' . $flatPlanFile->getClientOriginalName();
                $flatPlanPath = $flatPlanFile->storeAs('uploads', $originalFileName, 'public');
                $flatPlanPath = Storage::url($flatPlanPath);
            } else if ($request->input('flat_plan') === null) {
                if ($asset->flat_plan && Storage::disk('public')->exists($asset->flat_plan)) {
                    Storage::disk('public')->delete($asset->flat_plan);
                }
                $flatPlanPath = null;
            } else {
                $flatPlanPath = $request->flat_plan;
            }

            if ($request->hasFile('ownership_certificate')) {
                if ($asset->ownership_certificate && Storage::disk('public')->exists($asset->ownership_certificate)) {
                    Storage::disk('public')->delete($asset->ownership_certificate);
                }
                $ownershipCertificateFile = $request->file('ownership_certificate');
                $originalFileName = time() . '_' . $ownershipCertificateFile->getClientOriginalName();
                $ownershipCertificatePath = $ownershipCertificateFile->storeAs('uploads', $originalFileName, 'public');
                $ownershipCertificatePath = Storage::url($ownershipCertificatePath);

            } else if ($request->input('ownership_certificate') === null) {
                if ($asset->ownership_certificate && Storage::disk('public')->exists($asset->ownership_certificate)) {
                    Storage::disk('public')->delete($asset->ownership_certificate);
                }
                $ownershipCertificatePath = null;
            } else {
                $ownershipCertificatePath = $request->ownership_certificate;
            }

            if ($request->hasFile('renovation_agreement')) {
                if ($asset->renovation_agreement && Storage::disk('public')->exists($asset->renovation_agreement)) {
                    Storage::disk('public')->delete($asset->renovation_agreement);
                }
                $renovationAgreementFile = $request->file('renovation_agreement');
                $originalFileName = time() . '_' . $renovationAgreementFile->getClientOriginalName();
                $renovationAgreementPath = $renovationAgreementFile->storeAs('uploads', $originalFileName, 'public');
                $renovationAgreementPath = Storage::url($renovationAgreementPath);

            } else if ($request->input('renovation_agreement') === null) {
                if ($asset->renovation_agreement && Storage::disk('public')->exists($asset->renovation_agreement)) {
                    Storage::disk('public')->delete($asset->renovation_agreement);
                }
                $renovationAgreementPath = null;
            } else {
                $renovationAgreementPath = $request->renovation_agreement;
            }


        } else {
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }
            if ($request->hasFile('floor_plan')) {
                $floorPlanFile = $request->file('floor_plan');
                $originalFileName = time() . '_' . $floorPlanFile->getClientOriginalName();
                $floorPlanPath = $floorPlanFile->storeAs('uploads', $originalFileName, 'public');
                $floorPlanPath = Storage::url($floorPlanPath);
            }
            if ($request->hasFile('flat_plan')) {
                $flatPlanFile = $request->file('flat_plan');
                $originalFileName = time() . '_' . $flatPlanFile->getClientOriginalName();
                $flatPlanPath = $flatPlanFile->storeAs('uploads', $originalFileName, 'public');
                $flatPlanPath = Storage::url($flatPlanPath);
            }
            if ($request->hasFile('ownership_certificate')) {
                $ownershipCertificateFile = $request->file('ownership_certificate');
                $originalFileName = time() . '_' . $ownershipCertificateFile->getClientOriginalName();
                $ownershipCertificatePath = $ownershipCertificateFile->storeAs('uploads', $originalFileName, 'public');
                $ownershipCertificatePath = Storage::url($ownershipCertificatePath);
            }
            if ($request->hasFile('renovation_agreement')) {
                $renovationAgreementFile = $request->file('renovation_agreement');
                $originalFileName = time() . '_' . $renovationAgreementFile->getClientOriginalName();
                $renovationAgreementPath = $renovationAgreementFile->storeAs('uploads', $originalFileName, 'public');
                $renovationAgreementPath = Storage::url($renovationAgreementPath);
            }

        }


        $assetData = [
            'address' => $request->address,
            'cadastral_number' => $request->cadastral_number,
            'investor_id' => $request->investor_id,
            'city' => $request->city,
            'delivery_date' => $request->delivery_date,
            'area' => $request->area,
            'total_price' => $request->total_price,
            'icon' => $path,
            'floor_plan' => $floorPlanPath && $floorPlanPath !== 'null' ? $floorPlanPath : null,
            'flat_plan' => $flatPlanPath && $flatPlanPath !== 'null' ? $flatPlanPath : null,
            'agreement' => null,
            'ownership_certificate' => $ownershipCertificatePath && $ownershipCertificatePath !== 'null' ? $ownershipCertificatePath : null,
            'currency' => $request->currency,
            'project_name' => $request->project_name,
            'project_description' => $request->project_description,
            'total_floors' => $request->total_floors ?? null,
            'delivery_condition_description' => $request->delivery_condition_description,
            'project_link' => $request->project_link,
            'location' => $request->location,
            'type' => $request->type,
            'floor' => $request->floor,
            'flat_number' => $request->flat_number,
            'price' => $request->price,
            'condition' => $request->condition,
            'agreement_status' => $request->agreement_status,
            'agreement_date' => $request->agreement_date,
            'asset_status' => $request->asset_status,
            'first_payment_date' => $request->first_payment_date ?? null,
            'period' => $request->period ?? null,
            'current_value' => $request->current_value ?? null,
            'current_value_currency' => $request->current_value_currency ?? 'USD',
            'renovation_agreement' => $renovationAgreementPath && $renovationAgreementPath !== 'null' ? $renovationAgreementPath : null,
            'renovation_agreement_name' => $request->renovation_agreement_name,
            'renovation_agreement_date' => $request->renovation_agreement_date,
            'renovation_first_payment_date' => $request->renovation_first_payment_date ?? null,
            'renovation_period' => $request->renovation_period ?? null,
            'renovation_total_price' => $request->renovation_total_price ?? null,
            'renovation_status' => $request->renovation_status ?? 'Completed',
            'status' => 'completed',
            'developer_access' => $request->developer_access ?? 0,
        ];

        if (!$request->id) {
            $assetData['admin_id'] = Auth::user()->getAuthIdentifier();
        }

        $asset = Asset::updateOrCreate(['id' => $request->id], $assetData);

        $investorIds = explode(',', $request->investor_ids);

        $asset->investors()->sync($investorIds);

        if ($request->agreement_status === 'Installments') {
            if ($asset->payments) {
                $asset->payments()->delete();
            }
            if (json_decode($request->payments)) {
                foreach (json_decode($request->payments) as $payment) {
                    Payment::create([
                        'number' => $payment->number,
                        'payment_date' => $payment->payment_date,
                        'amount' => $payment->amount,
                        'left_amount' => $payment->amount,
                        'asset_id' => $asset->id
                    ]);
                }
                if ($asset->paymentsHistories) {
                    $totalPaid = $asset->paymentsHistories()->sum('amount');
                    $this->updatePaymentsHelper->updatePayments($asset, $totalPaid);
                }
            } else {
                $firstPaymentDate = Carbon::parse($request->input('first_payment_date'));
                $period = $request->input('period');
                $totalAmount = $request->input('total_price');
                $payments = $this->generatePaymentsList($firstPaymentDate, $period, $totalAmount);

                foreach ($payments as $payment) {
                    Payment::create([
                        'number' => $payment['number'],
                        'payment_date' => $payment['date'],
                        'amount' => $payment['amount'],
                        'left_amount' => $payment['amount'],
                        'asset_id' => $asset->id,
                        'status' => 0
                    ]);
                }
                if ($asset->paymentsHistories) {
                    $totalPaid = $asset->paymentsHistories()->sum('amount');
                    $this->updatePaymentsHelper->updatePayments($asset, $totalPaid);
                }
            }
        }

        if ($request->asset_status === 'Rented') {
            if ($request->tenant) {
                $tenantData = $request->tenant;

                $tenantDataArray = [
                    'name' => $tenantData['name'],
                    'email' => $tenantData['email'],
                    'phone' => $tenantData['phone'],
                    'surname' => $tenantData['surname'],
                    'id_number' => $tenantData['id_number'],
                    'citizenship' => $tenantData['citizenship'],
                    'agreement_date' => $tenantData['agreement_date'],
                    'agreement_term' => $tenantData['agreement_term'],
                    'monthly_rent' => $tenantData['monthly_rent'],
                    'currency' => $tenantData['currency'],
                    'prefix' => $tenantData['prefix'],
                    'asset_id' => $asset->id,
                    'representative' => $tenantData['representative'],
                    'status' => 1
                ];
                if (isset($tenantData['id'])) {
                    $tenant = Tenant::where('id', $tenantData['id'])->first();
                    $tenant->update(
                        $tenantDataArray
                    );
                } else {
                    $tenant = Tenant::create($tenantDataArray);
                }

                Tenant::where('asset_id', $asset->id)
                    ->where('id', '!=', $tenant->id)
                    ->update(['status' => 0]);

                if ($request->hasFile('tenant.passport')) {
                    $passportFile = $request->file('tenant.passport');
                    $filename = time() . '_' . $passportFile->getClientOriginalName();
                    $path = $passportFile->storeAs('uploads', $filename, 'public');
                    $path = Storage::url($path);

                    $tenant->passport = $path;
                    $tenant->save();
                }

                if ($request->hasFile('tenant.rent_agreement')) {
                    $rentAgreementFile = $request->file('tenant.rent_agreement');
                    $filename = time() . '_' . $rentAgreementFile->getClientOriginalName();
                    $path = $rentAgreementFile->storeAs('uploads', $filename, 'public');
                    $path = Storage::url($path);

                    $tenant->rent_agreement = $path;
                    $tenant->save();
                }
            }
            if ($asset->rentals) {
                $asset->rentals()->delete();
            }


            if (json_decode($request->rentals)) {
                foreach (json_decode($request->rentals) as $rental) {
                    Rental::create([
                        'number' => $rental->number,
                        'payment_date' => $rental->payment_date,
                        'amount' => $rental->amount,
                        'left_amount' => $rental->amount,
                        'asset_id' => $asset->id
                    ]);
                }

                if ($asset->rentalPaymentsHistories) {
                    $activeTenant = Tenant::where('asset_id', $asset->id)->where('status', 1)->orderByDesc('id')->first();
                    $totalPaid = 0;
                    if ($activeTenant->id) {
                        $totalPaid = $asset->rentalPaymentsHistories()->where('tenant_id', $activeTenant->id)->sum('amount');
                    }
                    $paymentHistories = $asset->rentalPaymentsHistories()->where('tenant_id', $activeTenant->id)->get();
                    foreach ($paymentHistories as $paymentsHistory) {
                        $this->updateRentalPaymentsHelper->updateRentalPayments($asset, $paymentsHistory->amount, $paymentsHistory, $paymentsHistory->month ?? 1);
                    }

                }
            } else {
                $firstPaymentDate = Carbon::parse($tenantData['agreement_date']);
                $period = $tenantData['agreement_term'];

                for ($i = 1; $i <= $period; $i++) {
                    Rental::create([
                        'number' => $i,
                        'amount' => $tenantData['monthly_rent'],
                        'payment_date' => $firstPaymentDate->copy()->addMonths($i),
                        'left_amount' => $tenantData['monthly_rent'],
                        'asset_id' => $asset->id
                    ]);
                }

                if ($asset->rentalPaymentsHistories) {
                    $activeTenant = Tenant::where('asset_id', $asset->id)->where('status', 1)->orderByDesc('id')->first();
                    $totalPaid = 0;
                    if ($activeTenant->id) {
                        $totalPaid = $asset->rentalPaymentsHistories()->where('tenant_id', $activeTenant->id)->sum('amount');
                    }
                    $paymentHistories = $asset->rentalPaymentsHistories()->where('tenant_id', $activeTenant->id)->get();
                    foreach ($paymentHistories as $paymentsHistory) {
                        $this->updateRentalPaymentsHelper->updateRentalPayments($asset, $paymentsHistory->amount, $paymentsHistory, $paymentsHistory->month ?? 1);
                    }
                }
            }
        }
        if ($asset->renovationPayments) {
            $asset->renovationPayments()->delete();
        }
        if (json_decode($request->renovation_payments)) {
            foreach (json_decode($request->renovation_payments) as $payment) {
                RenovationPayment::create([
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'left_amount' => $payment->amount,
                    'asset_id' => $asset->id
                ]);
            }
            if ($asset->renovationPaymentsHistories) {
                $renovationTotalPaid = $asset->renovationPaymentsHistories()->sum('amount');
                $this->updateRenovationPaymentsHelper->updatePayments($asset, $renovationTotalPaid);
            }
        }

        if ($request->current_value) {
            $currentValueLastItem = CurrentValue::where('asset_id', $asset->id)->orderByDesc('id')->first();
            if (!$currentValueLastItem || $currentValueLastItem->value != $request->current_value) {
                $currentValueAttachmentPath = null;
                if (isset($request->id)) {

                    if ($request->hasFile('current_value_attachment')) {
                        if ($currentValueLastItem && $currentValueLastItem->current_value_attachment && Storage::disk('public')->exists($currentValueLastItem->current_value_attachment)) {
                            Storage::disk('public')->delete($currentValueLastItem->current_value_attachment);
                        }
                        $currentValueAttachmentFile = $request->file('current_value_attachment');
                        $originalFileName = time() . '_' . $currentValueAttachmentFile->getClientOriginalName();
                        $currentValueAttachmentPath = $currentValueAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $currentValueAttachmentPath = Storage::url($currentValueAttachmentPath);

                    } else if ($request->input('current_value_attachment') === null) {
                        if ($currentValueLastItem && Storage::disk('public')->exists($currentValueLastItem->current_value_attachment)) {
                            Storage::disk('public')->delete($currentValueLastItem->current_value_attachment);
                        }
                        $currentValueAttachmentPath = null;
                    } else {
                        $currentValueAttachmentPath = $request->current_value_attachment;
                    }
                } else {
                    if ($request->hasFile('current_value_attachment')) {
                        $currentValueAttachmentFile = $request->file('current_value_attachment');
                        $originalFileName = time() . '_' . $currentValueAttachmentFile->getClientOriginalName();
                        $currentValueAttachmentPath = $currentValueAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $currentValueAttachmentPath = Storage::url($currentValueAttachmentPath);
                    }
                }

                CurrentValue::create([
                    'asset_id' => $asset->id,
                    'value' => $request->current_value,
                    'date' => now(),
                    'currency' => $request->current_value_currency,
                    'attachment' => $currentValueAttachmentPath
                ]);
            }
        }

        $asset->attachments()->delete();
        if ($request->has('attachments')) {
            foreach ($request->attachments as $key => $file) {

                if (gettype($file) == 'string') {
                    $path = $file;
                    $explodedFile = explode('/', $path);
                    $explodedFile = array_reverse($explodedFile);
                    $originalFileName = $explodedFile[0];
                } else {
                    $originalFileName = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('uploads', $originalFileName, 'public');
                    $path = Storage::url($path);
                }

                AssetAttachment::create([
                    'asset_id' => $asset->id,
                    'image' => $path,
                    'name' => $originalFileName,
                ]);
            }
        }


        $asset->gallery()->delete();
        if ($request->has('gallery')) {
            foreach ($request->gallery as $key => $file) {

                if (gettype($file) == 'string') {
                    $path = $file;
                    $explodedFile = explode('/', $path);
                    $explodedFile = array_reverse($explodedFile);
                    $originalFileName = $explodedFile[0];
                } else {
                    $originalFileName = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('uploads', $originalFileName, 'public');
                    $path = Storage::url($path);
                }

                AssetGallery::create([
                    'name' => $originalFileName,
                    'image' => $path,
                    'asset_id' => $asset->id
                ]);
                if ($key == 0) {
                    $asset->icon = $path;
                    $asset->save();
                }
            }
        }

        $asset->informations()->delete();

        if (!empty($request->extraDetails)) {
            foreach ($request->extraDetails as $detail) {
                $extraDetailAttachmentPath = null;

                if (isset($detail['attachment'])) {
                    if (isset($request->id)) {
                        if (gettype($detail['attachment']) == 'object') {
                            $extraDetailAttachmentFile = $detail['attachment'];
                            $originalFileName = time() . '_' . $extraDetailAttachmentFile->getClientOriginalName();
                            $extraDetailAttachmentPath = $extraDetailAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                            $extraDetailAttachmentPath = Storage::url($extraDetailAttachmentPath);
                        } else {
                            $extraDetailAttachmentPath = $detail['attachment'];
                        }
                    } else {
                        if (isset($detail['attachment'])) {
                            $extraDetailAttachmentFile = $detail['attachment'];
                            $originalFileName = time() . '_' . $extraDetailAttachmentFile->getClientOriginalName();
                            $extraDetailAttachmentPath = $extraDetailAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                            $extraDetailAttachmentPath = Storage::url($extraDetailAttachmentPath);
                        }
                    }
                }

                AssetInformation::create([
                    'key' => $detail['key'],
                    'provider' => $detail['provider'],
                    'value' => $detail['value'],
                    'attachment' => $extraDetailAttachmentPath,
                    'asset_id' => $asset->id
                ]);
            }
        }

        $asset->agreements()->delete();

        if (!empty($request->agreements)) {
            foreach ($request->agreements as $agreement) {
                $agreementAttachmentPath = null;

                if (isset($agreement['attachment']) && gettype($agreement['attachment']) == 'object') {
                    $agreementAttachmentFile = $agreement['attachment'];
                    $originalFileName = time() . '_' . $agreementAttachmentFile->getClientOriginalName();
                    $agreementAttachmentPath = $agreementAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                    $agreementAttachmentPath = Storage::url($agreementAttachmentPath);
                } else {
                    // Either use the existing attachment or set it to null
                    $agreementAttachmentPath = $agreement['attachment'] ?? null;
                }

                AssetAgreement::create([
                    'name' => $agreement['name'],
                    'attachment' => $agreementAttachmentPath,
                    'asset_id' => $asset->id
                ]);
            }
        }

        if ($request->id) {
            if ($originalData) {
                $this->assetCompareService->logAssetChanges($originalData, $asset, $adminId);
            }
            if ($originalAttachments) {
                $this->assetCompareService->logAttachmentChanges($originalAttachments, $asset->attachments, $asset, $adminId);
            }
            if ($originalInformations) {
                $this->assetCompareService->logInformationsChanges($originalInformations, $asset->informations()->get()->toArray(), $asset, $adminId);
            }
            if ($originalAgreements) {
                $this->assetCompareService->logAgreementChanges($originalAgreements, $asset->agreements()->get()->toArray(), $asset, $adminId);
            }
            if ($originalGallery) {
                $this->assetCompareService->logGalleryChanges($originalGallery, $asset->gallery, $asset, $adminId);
            }

            if ($originalRentals) {
                $this->assetCompareService->logRentalPaymentChanges($originalRentals, $asset->rentals()->get()->toArray(), $asset, $adminId);
            }

            if ($originalPayments) {
                $this->assetCompareService->logPaymentChanges($originalPayments, $asset->payments()->get()->toArray(), $asset, $adminId);
            }
        }

        return ServiceResponse::jsonNotification('Asset Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.create_data');

            $this->baseData['id'] = $id;

            $asset = Asset::where('id', $id)->first();
//            $investor = Investor::where('id', $asset->investor_id)->first();

            $investors = $asset->investors;
            $investorNames = [];
            foreach ($investors as $investor) {
                $investorNames[] = $investor->name . ' ' . $investor->surname;
            }
            $investorNames = implode(' / ', $investorNames);
            $this->baseData['extra'] = [
                'asset_name' => $asset->project_name,
                'asset_route' => route('asset.view', [$asset->id]),
                'investor_name' => $investorNames,
            ];

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::success($this->baseData));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function view($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.create_data');

            $this->baseData['id'] = $id;
            $asset = Asset::where('id', $id)->first();
            $this->baseData['name'] = $asset->project_name ?? '';

            $asset = Asset::where('id', $id)->first();
//            $investor = Investor::where('id', $asset->investor_id)->first();
            $investors = $asset->investors;
            $investorNames = [];
            foreach ($investors as $investor) {
                $investorNames[] = $investor->name . ' ' . $investor->surname;
            }
            $investorNames = implode(' / ', $investorNames);

            $this->baseData['extra'] = [
                'asset_edit_route' => route('asset.edit', [$asset->id]),
                'payments_route' => $asset->agreement_status === 'Installments' ? route('asset.payments.list', [$asset->id]) : null,
                'rentals_route' => $asset->asset_status === 'Rented' ? route('asset.rental.index', [$asset->id]) : null,
                'investments_route' => route('asset.investment.index', [$asset->id]),
                'renovation_route' => $asset->renovation_status === 'In Progress' ? route('asset.renovation.index', [$asset->id]) : null,
                'investor_name' => $investorNames,
                'asset_id' => $asset->id
            ];

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function investorView($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.create_data');

            $this->baseData['id'] = $id;
            $this->baseData['name'] = Asset::where('id', $id)->first()->toArray()['project_name'];
        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            Asset::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

    /**
     * Archive an asset
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive($id)
    {
        try {
            $asset = Asset::findOrFail($id);
            
            // Archive the asset
            $asset->is_archived = true;
            $asset->save();

            return response()->json([
                'success' => true,
                'message' => 'Asset has been archived successfully.',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Unarchive an asset
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unarchive($id)
    {
        try {
            $asset = Asset::findOrFail($id);
            
            // Unarchive the asset
            $asset->is_archived = false;
            $asset->save();

            return response()->json([
                'success' => true,
                'message' => 'Asset has been unarchived successfully.',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function generatePaymentsList($firstPaymentDate, $period, $totalAmount)
    {
        $payments = [];
        $amountPerPeriod = round($totalAmount / $period, 2);

        for ($i = 0; $i < $period; $i++) {
            $paymentDate = $firstPaymentDate->copy()->addMonths($i);
            $payments[] = [
                'number' => $i + 1,
                'date' => $paymentDate->format('Y/m/d'),
                'amount' => $amountPerPeriod
            ];
        }
        $payments[$period - 1]['amount'] = round($totalAmount - ($amountPerPeriod * ($period - 1)), 2);

        return $payments;
    }

    public function getAssetsToClone()
    {
        $assets = DB::table('assets')
            ->select('project_name', DB::raw('MAX(id) as id'))
            ->groupBy('project_name')
            ->get();

        $this->baseData['assets'] = $assets;

        return ServiceResponse::jsonNotification('Assets grouped list', 200, $this->baseData);
    }

    public function clone($name)
    {
        $asset = Asset::where('project_name', $name)->first();
        $this->baseData['asset'] = $asset;
        $this->baseData['gallery'] = AssetGallery::where('asset_id', $asset->id)->get();

        return ServiceResponse::jsonNotification('Assets grouped list', 200, $this->baseData);
    }

    public function filterOptions()
    {
        $this->baseData['investors'] = [];
        if (\Auth::guard('admin')->check()) {
            if (auth()->user()->getRolesNameAttribute() == 'administrator') {
                $this->baseData['investors'] = Investor::orderBy('name')
                    ->orderBy('surname')
                    ->get();
                $this->baseData['managers'] = Admin::whereHas('roles', function ($query) {
                    $query->where('name', 'like', '%asset%manager%');
                })->orderBy('name')
                    ->orderBy('surname')
                    ->get();

                $this->baseData['types'] = Asset::select('type', DB::raw('MAX(id) as max_id'))
                    ->groupBy('type')
                    ->orderBy('type')
                    ->get();

                $this->baseData['statuses'] = Asset::select('asset_status', DB::raw('MAX(id) as max_id'))
                    ->groupBy('asset_status')
                    ->orderBy('asset_status')
                    ->get();

                $this->baseData['assets'] = Asset::select('project_name', DB::raw('MAX(id) as max_id'))
                    ->groupBy('project_name')
                    ->orderBy('project_name')
                    ->get();
            } else {
                $this->baseData['investors'] = Investor::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->orderBy('name')
                    ->orderBy('surname')
                    ->get();
                $this->baseData['managers'] = [];

                $this->baseData['types'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('type', DB::raw('MAX(id) as max_id'))
                    ->groupBy('type')
                    ->orderBy('type')
                    ->get();


                $this->baseData['statuses'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('asset_status', DB::raw('MAX(id) as max_id'))
                    ->groupBy('asset_status')
                    ->orderBy('asset_status')
                    ->get();

                $this->baseData['assets'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('project_name', DB::raw('MAX(id) as max_id'))
                    ->groupBy('project_name')
                    ->orderBy('project_name')
                    ->get();
            }
        }


        return ServiceResponse::jsonNotification(__(''), 200, $this->baseData);
    }

    public function investorFilterOptions()
    {
        $user = \auth()->user();

        $this->baseData['assets'] = Asset::query()
            ->whereHas('investors', function ($q) use ($user) {
                $q->where('investor_id', $user->id);
            })
            ->select('project_name', DB::raw('MAX(id) as max_id'))
            ->groupBy('project_name')
            ->orderBy('project_name')
            ->get();

        $this->baseData['types'] = Asset::query()
            ->whereHas('investors', function ($q) use ($user) {
                $q->where('investor_id', $user->id);
            })
            ->select('type', DB::raw('MAX(id) as max_id'))
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        return ServiceResponse::jsonNotification(__(''), 200, $this->baseData);

    }

    public function developerFilterOptions()
    {
        $user = \auth()->user();
        $developerAssetNames = $user->assets()->pluck('asset_name')->toArray();

        $assets = Asset::whereIn('project_name', $developerAssetNames)->where('developer_access', true);

        $this->baseData['assets'] = (clone $assets)
            ->select('project_name', DB::raw('MAX(id) as max_id'))
            ->groupBy('project_name')
            ->orderBy('project_name')
            ->get();

        $this->baseData['types'] = (clone $assets)
            ->select('type', DB::raw('MAX(id) as max_id'))
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        $this->baseData['cities'] = (clone $assets)
            ->select('city', DB::raw('MAX(id) as max_id'))
            ->groupBy('city')
            ->orderBy('city')
            ->get();

        $assetIds = $assets->pluck('id')->unique();

        $investors = Investor::whereHas('assets', function ($q) use ($assetIds) {
            $q->whereIn('assets.id', $assetIds);
        })->get();
        $this->baseData['investors'] = $investors->toArray();

        $managers = [];
        foreach ($investors as $investor) {
            $managers[$investor->admin->id] = $investor->admin->toArray();
        }

        $this->baseData['managers'] = $managers;

        return ServiceResponse::jsonNotification(__(''), 200, $this->baseData);

    }

    public function sell(AssetSaleRequest $request, $assetId)
    {
        $path = null;

        if ($request->hasFile('sale_agreement')) {
            $file = $request->file('sale_agreement');
            $originalFileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $originalFileName, 'public');
            $path = Storage::url($path);
        } elseif (is_string($request->sale_agreement)) {
            $path = $request->sale_agreement;
        }
        Asset::where('id', $assetId)->first()->update([
            'sale_date' => $request->sale_date,
            'sale_status' => 'sold',
            'sale_price' => $request->sale_price,
            'purchaser' => $request->purchaser,
            'sale_agreement' => $path
        ]);

        return ServiceResponse::jsonNotification(__('Sale Registered successfully'), 200, $this->baseData);

    }

    public function deleteSell(Request $request, $assetId)
    {

        Asset::where('id', $assetId)->first()->update([
            'sale_date' => null,
            'sale_status' => 'active',
            'sale_price' => null,
            'purchaser' => null,
            'sale_agreement' => null
        ]);

        return ServiceResponse::jsonNotification(__('Sale Deleted successfully'), 200, $this->baseData);

    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'agreement_date',
            'payment_date',
            'investor',
            'status',
            'asset',
            'manager',
            'asset_status',
            'asset_type',
            'agreement_status',
        ]);
        if (Auth::guard('developer')->check()) {
            return Excel::download(new DeveloperAssetExport($filters), 'assets.xlsx');
        }

        return Excel::download(new AssetExport($filters), 'assets.xlsx');
    }


    public function saveProjectDetails(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'project_name' => 'required|string|max:255',
                'project_description' => 'nullable|string',
                'project_link' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'total_floors' => 'nullable|integer',
                'location' => 'nullable|string',
                'delivery_date' => 'nullable|date_format:Y/m/d',
                'investor_ids' => 'nullable|string',
            ]);

            $path = null;

            // Handle the icon/gallery if provided
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            } elseif ($request->has('icon') && !is_null($request->icon)) {
                $path = $request->icon;
            } elseif ($request->id) {
                // Keep existing icon for updates
                $asset = Asset::find($request->id);
                if ($asset) {
                    $path = $asset->icon;
                }
            }

            // Build asset data - only include project-related fields
            $assetData = [
                'project_name' => $request->project_name,
                'project_description' => $request->project_description,
                'project_link' => $request->project_link,
                'city' => $request->city,
                'address' => $request->address,
                'total_floors' => $request->total_floors,
                'location' => $request->location,
                'delivery_date' => $request->delivery_date,
                'status' => 'incomplete',
                'icon' => $path,
            ];

            // Set admin_id for new assets
            if (!$request->id) {
                $assetData['admin_id'] = Auth::user()->getAuthIdentifier();
            }

            // Create or update asset
            $asset = Asset::updateOrCreate(['id' => $request->id], $assetData);

            // Handle investors if provided
            if ($request->filled('investor_ids')) {
                $investorIds = explode(',', $request->investor_ids);
                $asset->investors()->sync($investorIds);
            }

            // Handle gallery files
            if ($request->has('gallery')) {
                $asset->gallery()->delete();
                foreach ($request->gallery as $key => $file) {
                    if (gettype($file) == 'string') {
                        $path = $file;
                        $explodedFile = explode('/', $path);
                        $explodedFile = array_reverse($explodedFile);
                        $originalFileName = $explodedFile[0];
                    } else {
                        $originalFileName = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('uploads', $originalFileName, 'public');
                        $path = Storage::url($path);
                    }

                    AssetGallery::create([
                        'name' => $originalFileName,
                        'image' => $path,
                        'asset_id' => $asset->id
                    ]);

                    if ($key == 0 && !$assetData['icon']) {
                        $asset->icon = $path;
                        $asset->save();
                    }
                }
            }

            // Return success response with the asset
            $this->baseData['id'] = $asset->id;
            return ServiceResponse::jsonNotification('Project details saved successfully', 200, $this->baseData);

        } catch (\Exception $ex) {
            return ServiceResponse::jsonNotification($ex->getMessage(), 500);
        }
    }

    public function developerAccess(Request $request, $assetId)
    {
        $asset = Asset::where('id', $assetId)->first();
        $asset->update(['developer_access' => !$asset->developer_access]);

        return redirect()->back();
    }

    /**
     * Calculate the paid amounts for each asset in the collection
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $assets
     * @param Request $request
     * @return void
     */
    private function calculatePaidAmounts($assets, Request $request)
    {
        foreach ($assets as $asset) {
            $payments = $asset->paymentsHistories ?: collect([]);

            // Check if date filters are active
            if ($request->has('agreement_date') && !is_null($request->agreement_date) && $request->agreement_date !== 'null') {
                $dateRange = explode(',', $request->agreement_date);
                $startDate = isset($dateRange[0]) ? date('Y-m-d', strtotime($dateRange[0])) : null;
                $endDate = isset($dateRange[1]) ? date('Y-m-d', strtotime($dateRange[1])) : null;

                if ($startDate) {
                    $payments = $payments->filter(function ($payment) use ($startDate) {
                        return isset($payment->payment_date) && strtotime($payment->payment_date) >= strtotime($startDate);
                    });
                }

                if ($endDate) {
                    $payments = $payments->filter(function ($payment) use ($endDate) {
                        return isset($payment->payment_date) && strtotime($payment->payment_date) <= strtotime($endDate);
                    });
                }
            }

            if ($asset->agreement_status === 'Installments') {
                $paid = $payments->sum('amount');
            } else {
                $paid = $asset->total_price;
            }

            // Calculate percentage
            $paidPercent = $asset->total_price > 0
                ? (fmod(($paid / $asset->total_price) * 100, 1) == 0
                    ? number_format(($paid / $asset->total_price) * 100, 0)
                    : number_format(($paid / $asset->total_price) * 100, 2))
                : '0';

            // Store calculated values with the asset
            $asset->paid_amount = $paid;
            $asset->paid_percent = $paidPercent;
            $asset->paid_formatted = number_format($paid, 0, ".", ",") . '$ - ' . $paidPercent . '%';
        }
    }
}
