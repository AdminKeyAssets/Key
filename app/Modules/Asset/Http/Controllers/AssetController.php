<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Helpers\UpdatePaymentsHelper;
use App\Modules\Asset\Http\Requests\AssetRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\AssetAttachment;
use App\Modules\Asset\Models\CurrentValue;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
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
    /**
     * @var UpdatePaymentsHelper
     */
    protected $updatePaymentsHelper;

    /**
     * @param UpdatePaymentsHelper $updatePaymentsHelper
     */
    public function __construct(
        UpdatePaymentsHelper $updatePaymentsHelper
    )
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->updatePaymentsHelper = $updatePaymentsHelper;
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

        if (in_array($user->getRolesNameAttribute(), $managers)) {
            $investors = Investor::where('admin_id', $userId)->pluck('id');
            $this->baseData['allData'] = Asset::whereIn('investor_id', $investors)->orderByDesc('id')->paginate(25);
        } else {
            $this->baseData['allData'] = Asset::orderByDesc('id')->paginate(25);
        }

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
                    $investor = Investor::where('id', $asset->investor_id)->first();
                    $salesManager = Admin::where('id', $investor->admin_id)->first();
                }

                if ($asset->payments) {
                    $nextPayment = $asset->payments->where('status', 0)->first();
                }

                $this->baseData['item'] = $asset;
                $this->baseData['item']['extraDetails'] = $asset->informations;
                $this->baseData['item']['agreements'] = $asset->agreements;
                $this->baseData['item']['gallery'] = $asset->gallery;
                $this->baseData['item']['payments'] = $asset->payments;
                $this->baseData['item']['payments_histories'] = $asset->paymentsHistories;
                $tenant = Tenant::where('asset_id', $asset->id)->where('status', true)->first();
                $this->baseData['item']['tenant'] = $this->baseData['item']['rental_payments_histories'] = [];
                if ($tenant) {
                    $this->baseData['item']['tenant'] = $tenant;
                    $this->baseData['item']['rental_payments_histories'] = RentalPaymentsHistory::where('asset_id', $asset->id)->where('tenant_id', $tenant->id)->get();
//                    dd($this->baseData['item']['rental_payments_histories']);
                }
                $this->baseData['item']['rentals'] = $asset->rentals;
                $this->baseData['item']['currentValues'] = $asset->currentValues;
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
            }
            $this->baseData['item']['countries'] = Country::get('country');
            $this->baseData['item']['prefixes'] = Country::groupBy('prefix')->get('prefix');
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
//        dd($request->id);
        if (isset($request->id)) {
            $asset = Asset::where('id', $request->id)->first();
            if ($request->hasFile('icon')) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }

                $file = $request->file('icon');
                $originalFileName = $file->getClientOriginalName();
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
                $originalFileName = $floorPlanFile->getClientOriginalName();
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
                $originalFileName = $flatPlanFile->getClientOriginalName();
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
                $originalFileName = $ownershipCertificateFile->getClientOriginalName();
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

        } else {
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $originalFileName = $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }
            if ($request->hasFile('floor_plan')) {
                $floorPlanFile = $request->file('floor_plan');
                $originalFileName = $floorPlanFile->getClientOriginalName();
                $floorPlanPath = $floorPlanFile->storeAs('uploads', $originalFileName, 'public');
                $floorPlanPath = Storage::url($floorPlanPath);
            }
            if ($request->hasFile('flat_plan')) {
                $flatPlanFile = $request->file('flat_plan');
                $originalFileName = $flatPlanFile->getClientOriginalName();
                $flatPlanPath = $flatPlanFile->storeAs('uploads', $originalFileName, 'public');
                $flatPlanPath = Storage::url($flatPlanPath);
            }
            if ($request->hasFile('ownership_certificate')) {
                $ownershipCertificateFile = $request->file('ownership_certificate');
                $originalFileName = $ownershipCertificateFile->getClientOriginalName();
                $ownershipCertificatePath = $ownershipCertificateFile->storeAs('uploads', $originalFileName, 'public');
                $ownershipCertificatePath = Storage::url($ownershipCertificatePath);
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
            'admin_id' => Auth::user()->getAuthIdentifier(),
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
            'current_value' => $request->current_value ?? 0,
            'current_value_currency' => $request->current_value_currency ?? 'USD'
        ];


        $asset = Asset::updateOrCreate(['id' => $request->id], $assetData);

        if ($request->agreement_status === 'Installments') {
            if ($asset->payments) {
                $asset->payments()->delete();
            }
            if ($request->payments) {
                foreach (json_decode($request->payments) as $payment) {
                    Payment::create([
                        'number' => $payment->number,
                        'payment_date' => $payment->payment_date,
                        'amount' => $payment->amount,
                        'left_amount' => $payment->amount,
                        'asset_id' => $asset->id
                    ]);
                }
                if($asset->paymentsHistories){
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
                        'month' => $payment['number'],
                        'payment_date' => $payment['date'],
                        'amount' => $payment['amount'],
                        'left_amount' => $payment['amount'],
                        'asset_id' => $asset->id
                    ]);
                }
                if($asset->paymentsHistories){
                    $totalPaid = $asset->paymentsHistories()->sum('amount');
                    $this->updatePaymentsHelper->updatePayments($asset, $totalPaid);
                }
            }
        }

        if ($request->asset_status === 'Rented') {
            if ($request->tenant) {
                $tenantData = $request->tenant;

                $tenant = Tenant::updateOrCreate([
                    'email' => $tenantData['email'],
                    'phone' => $tenantData['phone'],
                ],
                    [
                        'name' => $tenantData['name'],
                        'surname' => $tenantData['surname'],
                        'id_number' => $tenantData['id_number'],
                        'citizenship' => $tenantData['citizenship'],
                        'agreement_date' => $tenantData['agreement_date'],
                        'agreement_term' => $tenantData['agreement_term'],
                        'monthly_rent' => $tenantData['monthly_rent'],
                        'currency' => $tenantData['currency'],
                        'prefix' => $tenantData['prefix'],
                        'asset_id' => $asset->id,
                    ]);

                if ($request->hasFile('tenant.passport')) {
                    $passportFile = $request->file('tenant.passport');
                    $filename = $passportFile->getClientOriginalName();
                    $path = $passportFile->storeAs('uploads', $filename, 'public');
                    $path = Storage::url($path);

                    $tenant->passport = $path;
                    $tenant->save();
                }
            }
            if ($asset->rentals) {
                $asset->rentals()->delete();
            }
            if ($request->rentals) {

                foreach (json_decode($request->rentals) as $rental) {
                    Rental::create([
                        'number' => $rental->number,
                        'payment_date' => $rental->payment_date,
                        'amount' => $rental->amount,
                        'left_amount' => $rental->amount,
                        'asset_id' => $asset->id
                    ]);
                }
            } else {
                $firstPaymentDate = Carbon::parse($tenantData['agreement_date']);
                $period = $tenantData['agreement_term'];

                for ($i = 1; $i <= $period; $i++) {
                    Rental::create([
                        'month' => $i,
                        'payment_date' => $firstPaymentDate->copy()->addMonths($i),
                        'left_amount' => $tenantData['monthly_rent'],
                        'asset_id' => $asset->id
                    ]);
                }
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
                        $originalFileName = $currentValueAttachmentFile->getClientOriginalName();
                        $currentValueAttachmentPath = $currentValueAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $currentValueAttachmentPath = Storage::url($currentValueAttachmentPath);

                    } else if ($request->input('current_value_attachment') === null) {
                        if ($currentValueLastItem->current_value_attachment && Storage::disk('public')->exists($currentValueLastItem->current_value_attachment)) {
                            Storage::disk('public')->delete($currentValueLastItem->current_value_attachment);
                        }
                        $currentValueAttachmentPath = null;
                    } else {
                        $currentValueAttachmentPath = $request->current_value_attachment;
                    }
                } else {
                    if ($request->hasFile('current_value_attachment')) {
                        $currentValueAttachmentFile = $request->file('current_value_attachment');
                        $originalFileName = $currentValueAttachmentFile->getClientOriginalName();
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

        if ($request->has('gallery')) {
            $asset->gallery()->delete();
            foreach ($request->gallery as $key => $file) {

                if (gettype($file) == 'string') {
                    $path = $file;
                    $explodedFile = explode('/', $path);
                    $explodedFile = array_reverse($explodedFile);
                    $originalFileName = $explodedFile[0];
                } else {
                    $originalFileName = $file->getClientOriginalName();
                    $path = $file->storeAs('uploads', $originalFileName, 'public');
                    $path = Storage::url($path);
                }

                $asset->gallery()->create([
                    'name' => $originalFileName,
                    'image' => $path
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
                            $originalFileName = $extraDetailAttachmentFile->getClientOriginalName();
                            $extraDetailAttachmentPath = $extraDetailAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                            $extraDetailAttachmentPath = Storage::url($extraDetailAttachmentPath);
                        } else {
                            $extraDetailAttachmentPath = $detail['attachment'];
                        }
                    } else {
                        if (isset($detail['attachment'])) {
                            $extraDetailAttachmentFile = $detail['attachment'];
                            $originalFileName = $extraDetailAttachmentFile->getClientOriginalName();
                            $extraDetailAttachmentPath = $extraDetailAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                            $extraDetailAttachmentPath = Storage::url($extraDetailAttachmentPath);
                        }
                    }
                }

                $asset->informations()->create([
                    'key' => $detail['key'],
                    'value' => $detail['value'],
                    'attachment' => $extraDetailAttachmentPath
                ]);
            }
        }

        $asset->agreements()->delete();

        if (!empty($request->agreements)) {
            foreach ($request->agreements as $agreement) {
                $agreementAttachmentPath = null;

                if (isset($request->id)) {
                    if (gettype($agreement['attachment']) == 'object') {
                        $agreementAttachmentFile = $agreement['attachment'];
                        $originalFileName = $agreementAttachmentFile->getClientOriginalName();
                        $agreementAttachmentPath = $agreementAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $agreementAttachmentPath = Storage::url($agreementAttachmentPath);
                    } else {
                        $agreementAttachmentPath = $agreement['attachment'];
                    }
                } else {
                    if (isset($detail['attachment'])) {
                        $agreementAttachmentFile = $detail['attachment'];
                        $originalFileName = $agreementAttachmentFile->getClientOriginalName();
                        $agreementAttachmentPath = $agreementAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $agreementAttachmentPath = Storage::url($agreementAttachmentPath);
                    }
                }

                $asset->agreements()->create([
                    'name' => $agreement['name'],
                    'attachment' => $agreementAttachmentPath
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
            $asset = Asset::where('id', $id)->first();
            $this->baseData['name'] = $asset->project_name ?? '';

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

    public function generatePaymentsList($firstPaymentDate, $period, $totalAmount)
    {
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

        return $payments;
    }
}
