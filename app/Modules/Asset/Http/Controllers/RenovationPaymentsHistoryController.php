<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\PaymentHistoryExport;
use App\Modules\Admin\Exports\PaymentScheduleExport;
use App\Modules\Admin\Exports\RenovationPaymentScheduleExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Helpers\UpdatePaymentsHelper;
use App\Modules\Asset\Helpers\UpdateRenovationPaymentsHelper;
use App\Modules\Asset\Http\Requests\PaymentRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\CurrentValue;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\PaymentsHistory;
use App\Modules\Asset\Models\RenovationPayment;
use App\Modules\Asset\Models\RenovationPaymentsHistory;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

class RenovationPaymentsHistoryController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'renovation';

    /**
     * @var string
     */
    public $baseName = 'renovation.';
    /**
     * @var UpdateRenovationPaymentsHelper
     */
    protected $paymentsHelper;

    public function __construct(
        UpdateRenovationPaymentsHelper $paymentsHelper
    )
    {
        parent::__construct();
        $this->paymentsHelper = $paymentsHelper;
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }


    /**
     * @param Request $request
     * @param $assetId
     * @return Application|Factory|View
     */
    public function index(Request $request, $assetId)
    {
        $this->baseData['allData'] = RenovationPaymentsHistory::where('asset_id', $assetId)->orderByDesc('id')->paginate(25);
        $this->baseData['assetId'] = $assetId;

        $asset = Asset::where('id', $assetId)->first();
//        $investor = Investor::where('id', $asset->investor_id)->first();
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

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param $assetId
     * @return Application|Factory|View
     */
    public function create($assetId)
    {
        $this->baseData['assetId'] = $assetId;

        $asset = Asset::where('id', $assetId)->first();
//        $investor = Investor::where('id', $asset->investor_id)->first();
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

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', $this->baseData);
    }

    /**
     * @param Request $request
     * @param $assetId
     * @return JsonResponse
     */
    public function createData(Request $request, $assetId)
    {
        try {
            $this->baseData['routes'] = [
                'create' => route('asset.renovation.create', $assetId),
                'create_data' => route('asset.renovation.create_data', $assetId),
                'save' => route('asset.renovation.store', $assetId),
                'edit' => route('asset.renovation.edit', $assetId, []),
            ];

//            $nextPayment = RenovationPayment::where('asset_id', $assetId)->where('status', 0)->orderByDesc('id')->first();
            $this->baseData['nextPayment'] = strtotime(RenovationPayment::where('asset_id', $assetId)->where('status', 0)->first()->payment_date) < time() ?
                RenovationPayment::where('asset_id', $assetId)
                    ->where('status', 0)
                    ->where('payment_date', '<', now()) // Using Laravel's helper for current date/time
                    ->sum('left_amount') : RenovationPayment::where('asset_id', $assetId)->where('status', 0)->first()->left_amount;



            if ($request->get('id')) {
                $payment = RenovationPaymentsHistory::where('id', $request->get('id'))->where('asset_id', $assetId)->first();

                $this->baseData['item'] = $payment;
            }


        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param PaymentRequest $request
     * @param $assetId
     * @return JsonResponse
     */
    public function store(PaymentRequest $request, $assetId)
    {
        $path = null;

        $currentValue = CurrentValue::where('asset_id', $assetId)->orderByDesc('id')->first();
        $asset = Asset::where('id', $assetId)->first();

        if (isset($request->id)) {
            $paymentHistory = RenovationPaymentsHistory::where('id', $request->id)->first();

            $oldAmount = $paymentHistory->amount;
            $newAmount = $request->amount;

            if ($request->hasFile('attachment')) {
                if ($paymentHistory->attachment && Storage::disk('public')->exists($paymentHistory->attachment)) {
                    Storage::disk('public')->delete($paymentHistory->attachment);
                }

                $file = $request->file('attachment');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);

            } else if ($request->input('attachment') === null) {
                if ($paymentHistory->attachment && Storage::disk('public')->exists($paymentHistory->attachment)) {
                    Storage::disk('public')->delete($paymentHistory->attachment);
                }
            } else {
                $path = $paymentHistory->attachment;
            }

            $paymentHistory->update([
                'asset_id' => $assetId,
                'date' => $request->date,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path
            ]);

            $amountToAddCurrentValue = 0;

            if ($oldAmount != $newAmount) {
                $amountToAddCurrentValue = $newAmount - $oldAmount;
            }

            $currentValueAmount = $currentValue->value + $amountToAddCurrentValue;

            if ($amountToAddCurrentValue) {
                $currentValue->update([
                    'value' => $currentValueAmount
                ]);
            }
//dd(1);
            $asset->update(['current_value' => $currentValueAmount]);

            $this->paymentsHelper->recalculatePaymentsAfterEdit($paymentHistory->asset, $oldAmount, $request->amount);
        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }

            $paymentHistory = RenovationPaymentsHistory::create([
                'asset_id' => $assetId,
                'date' => $request->date,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path
            ]);

            $currentValueAmount = $currentValue->value + $request->amount;

            $currentValue->update([
                'value' => $currentValueAmount
            ]);

            $asset->update(['current_value' => $currentValueAmount]);

            $this->paymentsHelper->updatePayments($paymentHistory->asset, $paymentHistory->amount);
        }

        $this->baseData['item'] = $paymentHistory;

        return ServiceResponse::jsonNotification('Payment Added successfully', 200, $this->baseData);
    }

    /**
     * @param $assetId
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($assetId, $id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.renovation.create_data', $assetId);

            $this->baseData['assetId'] = $assetId;
            $this->baseData['id'] = $id;

            $asset = Asset::where('id', $assetId)->first();
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
     * @param $assetId
     * @param $id
     * @return Application|Factory|View
     */
    public function view($assetId, $id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.renovation.create_data', $assetId);

            $this->baseData['assetId'] = $assetId;
            $this->baseData['id'] = $id;

            $asset = Asset::where('id', $assetId)->first();
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
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @param $assetId
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            $paymentHistory = RenovationPaymentsHistory::findOrFail($request->get('id'));
            $amount = $paymentHistory->amount;
            $asset = $paymentHistory->asset;
            $paymentHistory->delete();

            $currentValue = CurrentValue::where('asset_id', $asset->id)->orderByDesc('id')->first();
            $currentValueAmount = $currentValue->value - $amount;
            $currentValue->update([
                'value' => $currentValueAmount
            ]);
            $asset->update(['current_value' => $currentValueAmount]);

            $this->paymentsHelper->recalculatePaymentsAfterDeletion($asset, $amount);

            if ($asset->renovationPaymentsHistories()->count() == 0) {
                $asset->agreement_status = 'Installments';
                $asset->save();
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }


    public function export(Request $request, $assetId)
    {
        $filters = ['asset_id' => $assetId];

        return Excel::download(new RenovationPaymentScheduleExport($filters), 'renovation_payments_schedule.xlsx');
    }

    public function exportHistory(Request $request, $assetId)
    {

        $filters = ['asset_id' => $assetId];

        return Excel::download(new RenovationPaymentScheduleExport($filters), 'renovation_payments_history.xlsx');
    }
}
