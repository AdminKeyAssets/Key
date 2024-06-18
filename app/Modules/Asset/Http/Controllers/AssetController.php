<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Http\Requests\AssetRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\AssetAttachment;
use App\Modules\Asset\Models\Payment;
use App\Utilities\ServiceResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mockery\Exception;

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

    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $this->baseData['allData'] = Asset::orderByDesc('id')->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function myassets(Request $request)
    {
        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'SalesManager'];
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        $this->baseData['allData'] = Asset::where('investor_id', $userId)->orderByDesc('id')->paginate(25);


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
                $nextPayment = null;

                if ($asset->investor_id) {
                    $salesManager = Admin::where('id', $asset->admin_id)->first();
                }

                if ($asset->payments) {
                    $nextPayment = $asset->payments->where('status', 0)->first();
                }

                $this->baseData['item'] = $asset;
                $this->baseData['item']['extraDetails'] = $asset->informations;
                $this->baseData['item']['payments'] = $asset->payments;
                $this->baseData['salesManager'] = $salesManager;
                $this->baseData['nextPayment'] = $nextPayment;

                $files = [];
                if ($asset->attachments) {
                    foreach ($asset->attachments as $item) {
                        $files[] = [
                            'name' => $item->name,
                            'path' => Storage::url($item->path),
                            'type' => substr($item->type, 0, 5) == 'image' ? 'image' : null
                        ];
                    }
                }
                $this->baseData['item']['files'] = $files;
                if ($asset->icon) {
                    $this->baseData['item']['icon'] = Storage::url($asset->icon);
                }
                if ($asset->agreement) {
                    $this->baseData['item']['agreement'] = Storage::url($asset->agreement);
                }
                if ($asset->floor_plan) {
                    $this->baseData['item']['floor_plan'] = Storage::url($asset->floor_plan);
                }
                if ($asset->flat_plan) {
                    $this->baseData['item']['flat_plan'] = Storage::url($asset->flat_plan);
                }
                if ($asset->ownership_certificate) {
                    $this->baseData['item']['ownership_certificate'] = Storage::url($asset->ownership_certificate);
                }
            }
            $this->baseData['investors'] = Investor::get(['name', 'surname', 'id']);
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
        $path = $floorPlanPath = $flatPlanPath = $agreementPath = $ownershipCertificatePath = null;
        if (isset($request->id)) {
            $asset = Asset::where('id', $request->id)->first();
            if ($request->hasFile('icon')) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }

                $file = $request->file('icon');
                $path = $file->store('uploads', 'public');
            } else if ($request->input('icon') === null) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }
                $path = null;
            }

        } else {
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $path = $file->store('uploads', 'public');
            }
            if ($request->hasFile('floor_plan')) {
                $floorPlanFile = $request->file('floor_plan');
                $floorPlanPath = $floorPlanFile->store('uploads', 'public');
            }
            if ($request->hasFile('flat_plan')) {
                $flatPlanFile = $request->file('flat_plan');
                $flatPlanPath = $flatPlanFile->store('uploads', 'public');
            }
            if ($request->hasFile('agreement')) {
                $agreementFile = $request->file('agreement');
                $agreementPath = $agreementFile->store('uploads', 'public');
            }
            if ($request->hasFile('ownership_certificate')) {
                $ownershipCertificateFile = $request->file('ownership_certificate');
                $ownershipCertificatePath = $ownershipCertificateFile->store('uploads', 'public');
            }
        }

        $assetData = [
            'name' => $request->name,
            'address' => $request->address,
            'cadastral_number' => $request->cadastral_number,
            'investor_id' => $request->investor_id,
            'city' => $request->city,
            'delivery_date' => $request->delivery_date,
            'area' => $request->area,
            'total_price' => $request->total_price,
            'icon' => $path,
            'floor_plan' => $floorPlanPath,
            'flat_plan' => $flatPlanPath,
            'agreement' => $agreementPath,
            'ownership_certificate' => $ownershipCertificatePath,
            'admin_id' => Auth::user()->getAuthIdentifier(),
            'currency' => $request->currency,
            'project_name' => $request->project_name,
            'project_description' => $request->project_description,
            'project_link' => $request->project_link,
            'location' => $request->location,
            'type' => $request->type,
            'floor' => $request->floor,
            'flat_number' => $request->flat_number,
            'price' => $request->price,
            'condition' => $request->condition,
            'agreement_status' => $request->agreement_status,
            'agreement_date' => $request->agreement_date,
            'first_payment_date' => $request->first_payment_date ?? null,
            'period' => $request->period ?? null,
            'total_agreement_price' => $request->total_agreement_price ?? null,
        ];
//        dd(1);

        $asset = Asset::updateOrCreate(['id' => $request->id], $assetData);

        if ($request->agreement_status === 'Installments') {
            if($asset->payments){
                $asset->payments()->delete();
            }
            if ($request->payments) {
                foreach (json_decode($request->payments) as $payment) {
                    Payment::create([
                        'number' => $payment->number,
                        'payment_date' => $payment->payment_date,
                        'amount' => $payment->amount,
                        'asset_id' => $asset->id
                    ]);
                }
            }else{
                $firstPaymentDate = Carbon::parse($request->input('first_payment_date'));
                $period = $request->input('period');
                $totalAmount = $request->input('total_agreement_price');

                $payments = [];
                $amountPerPeriod = round($totalAmount / $period, 2);

                for ($i = 0; $i < $period; $i++) {
                    $paymentDate = $firstPaymentDate->copy()->addMonths($i);
                    $payments[] = [
                        'number' => $i + 1,
                        'date' => $paymentDate->toDateString(),
                        'amount' => $amountPerPeriod
                    ];
                }
                $payments[$period - 1]['amount'] = round($totalAmount - ($amountPerPeriod * ($period - 1)), 2);

                foreach ($payments as $payment) {
                    Payment::create([
                        'month' => $payment->number,
                        'payment_date' => $payment->date,
                        'amount' => $payment->amount
                    ]);
                }
            }
        }

        if ($request->has('attachmentsToRemove')) {
            $attachmentsToRemove = json_decode($request->attachmentsToRemove, true);
            foreach ($attachmentsToRemove as $attachmentId) {
                $attachment = AssetAttachment::find($attachmentId);
                if ($attachment) {
                    Storage::disk('public')->delete($attachment->path);
                    $attachment->delete();
                }
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('uploads', 'public');
                AssetAttachment::create([
                    'asset_id' => $asset->id,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType()
                ]);
            }
        }

        $asset->informations()->delete();
        if (!empty($request->extraDetails)) {
            foreach (json_decode($request->extraDetails) as $info) {
                $asset->informations()->create([
                    'key' => $info->key,
                    'value' => $info->value,
                ]);
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

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
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

    public function change($assetId)
    {
        $asset = Asset::where('id', $assetId)->first();

        $this->baseData['salesManagers'] = Admin::role(['Asset Manager'])->get(['name', 'id']);
        $this->baseData['managerId'] = $asset->admin_id;
        $this->baseData['assetId'] = $assetId;
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.manager', $this->baseData);
    }

    public function storeManager(Request $request)
    {
        $asset = Asset::where(['id' => $request->id])->update(['admin_id' => $request->admin_id]);

        return ServiceResponse::jsonNotification('Asset Added successfully', 200, $this->baseData);
    }
}
