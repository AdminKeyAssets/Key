<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\RentsPaymentsExport;
use App\Modules\Admin\Exports\RentsScheduleExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Helpers\UpdateRentalPaymentsHelper;
use App\Modules\Asset\Http\Requests\LeaseRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
use App\Utilities\ServiceResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

class RentalPaymentsHistoryController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'rental';

    public $baseName = 'rental.';
    /**
     * @var UpdateRentalPaymentsHelper
     */
    protected $paymentsHelper;

    public function __construct(
        UpdateRentalPaymentsHelper $paymentsHelper
    )
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->paymentsHelper = $paymentsHelper;
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request, $assetId)
    {
        $this->baseData['allData'] = RentalPaymentsHistory::where('asset_id', $assetId)->where('status', 1)->orderByDesc('id')->paginate(25);
        $this->baseData['assetId'] = $assetId;
        $asset = Asset::where('id', $assetId)->first();

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
     * @return JsonResponse
     */
    public function createData(Request $request, $assetId)
    {
        try {
            $this->baseData['routes'] = [
                'create' => route('asset.rental.create', $assetId),
                'create_data' => route('asset.rental.create_data', $assetId),
                'save' => route('asset.rental.store', $assetId),
                'edit' => route('asset.rental.edit', $assetId, []),
            ];

            $rentalsToPay = Rental::where('asset_id', $assetId)->where('status', 0)->get('number');
            $this->baseData['rentals'] = $rentalsToPay;
//            $nextPayment = Rental::where('asset_id', $assetId)->where('status', 0)->orderByDesc('id')->first();

            $this->baseData['nextPayment'] = strtotime(Rental::where('asset_id', $assetId)->where('status', 0)->first()->payment_date) < time() ?
                Rental::where('asset_id', $assetId)
                    ->where('status', 0)
                    ->where('payment_date', '<', date('Y/m/d'))
                    ->sum('left_amount') : Rental::where('asset_id', $assetId)->where('status', 0)->first()->left_amount;

            $existingPayments = RentalPaymentsHistory::where('asset_id', $assetId)->where('status', 1)->sum('amount');
            $totalRentals = Rental::where('asset_id', $assetId)->sum('amount');
            $this->baseData['totalAmountToPay'] = $totalRentals - $existingPayments;

            if ($request->get('id')) {
                $rental = RentalPaymentsHistory::findOrFail($request->get('id'));
                $this->baseData['item'] = $rental;
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param LeaseRequest $request
     * @return JsonResponse
     */
    public function store(LeaseRequest $request, $assetId)
    {
        $message = 'Payment Added successfully';
        // Calculate the total expected rental amount for the asset.
        $totalRentals = Rental::where('asset_id', $assetId)->sum('amount');

        // Check if updating an existing payment.
        if (isset($request->id)) {
            $paymentHistory = RentalPaymentsHistory::findOrFail($request->id);
            // Sum of all payments minus the payment we're about to update.
            $existingPayments = RentalPaymentsHistory::where('asset_id', $assetId)->where('status', 1)->sum('amount') - $paymentHistory->amount;

            if (($existingPayments + $request->amount) == $totalRentals) {

                // Uncomment to return complete rent
//                $request = new Request([
//                    'completionDate' => $request->date
//                ]);

//                $this->complete($request, $assetId);

                $this->baseData['redirect_to'] = route('asset.index');
                $message = 'Rent completed. Please complete the rent or prolong the rents schedule;';
            }

            if (($existingPayments + $request->amount) > $totalRentals) {
                return response()->json([
                    'errors' => [
                        'amount' => ['Payment Exceeds the Sum of the Installments Schedule. Please correct the amount, or alter the schedule']
                    ]
                ], 422);
            }
        } else {
            // Check for new payment.
            $existingPayments = RentalPaymentsHistory::where('asset_id', $assetId)->where('status', 1)->sum('amount');


            if (($existingPayments + $request->amount) == $totalRentals) {
                // Uncomment to return complete rent

//                $request = new Request([
//                    'completionDate' => $request->date
//                ]);
//
//
//                $this->complete($request, $assetId);

                $this->baseData['redirect_to'] = route('asset.index');

                $message = 'Rent completed. Please complete the rent or prolong the rents schedule;';
            }
            if (($existingPayments + $request->amount) > $totalRentals) {
                return response()->json([
                    'errors' => [
                        'amount' => ['Payment Exceeds the Sum of the Installments Schedule. Please correct the amount, or alter the schedule']
                    ]
                ], 422);
            }
        }

        $path = null;

        if (isset($request->id)) {
            $paymentHistory = RentalPaymentsHistory::findOrFail($request->id);
            $oldAmount = $paymentHistory->amount;

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
                'attachment' => $path,
                'month' => $request->month ?? null
            ]);

            $startMonth = $request->month ?? Rental::where('asset_id', $assetId)
                ->where('status', 0)->first()->number;

            $this->paymentsHelper->recalculateRentalPaymentsAfterEdit(
                $paymentHistory->asset,
                $paymentHistory,
                $oldAmount,
                $request->amount,
                $startMonth
            );
        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }

            $asset = Asset::where('id', $assetId)->first();
            $tenant = Tenant::where('asset_id', $asset->id)
                ->where('status', 1)
                ->orderByDesc('id')->first();

            $paymentHistory = RentalPaymentsHistory::create([
                'asset_id' => $assetId,
                'date' => $request->date,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path,
                'tenant_id' => $tenant->id,
                'month' => $request->month ?? null
            ]);

            $startMonth = $request->month ?? Rental::where('asset_id', $assetId)
                ->where('status', 0)->first()->number;

            $this->paymentsHelper->updateRentalPayments(
                $paymentHistory->asset,
                $paymentHistory->amount,
                $paymentHistory,
                $startMonth
            );
        }

        $this->baseData['item'] = $paymentHistory;

        return ServiceResponse::jsonNotification($message, 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($assetId, $id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.rental.create_data', $assetId);

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
     * @param $id
     * @return Application|Factory|View
     */
    public function view($assetId, $id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.rental.create_data', $assetId);

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
     * @return JsonResponse
     */
    public function destroy(Request $request, $assetId)
    {
        try {
            $paymentHistory = RentalPaymentsHistory::findOrFail($request->get('id'));
            $amount = $paymentHistory->amount;
            $asset = $paymentHistory->asset;
            $paymentHistory->delete();

            $this->paymentsHelper->recalculateRentalPaymentsAfterDeletion($asset, $paymentHistory);
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

    public function calculateRentalCost($price, $fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        $totalMonths = ($to->year - $from->year) * 12 + ($to->month - $from->month);

        $dayDifference = $to->day - $from->day;

        $daysInFromMonth = $from->daysInMonth;

        if ($dayDifference < 0) {
            $totalMonths -= 1;
            $fractionOfMonth = ($dayDifference + $daysInFromMonth) / $daysInFromMonth;
        } else {
            $fractionOfMonth = $dayDifference / $to->daysInMonth;
        }

        $exactMonthDifference = $totalMonths + $fractionOfMonth;

        return $price * $exactMonthDifference;
    }

    public function export(Request $request, $assetId)
    {
        $filters = ['asset_id' => $assetId];
        return Excel::download(new RentsScheduleExport($filters), 'rents_schedule.xlsx');
    }

    public function exportHistory(Request $request, $assetId)
    {
        $filters = ['asset_id' => $assetId];
        return Excel::download(new RentsPaymentsExport($filters), 'rentals_payments.xlsx');
    }

    public function complete(Request $request, $assetId)
    {
        $asset = Asset::where('id', $assetId)->first();
        $asset->asset_status = 'Vacant';

        $completionDate = $request->get('completionDate') ?: Carbon::today()->format('Y-m-d');

        $tenant = Tenant::where('asset_id', $asset->id)->where('status', 1)->orderByDesc('id')->first();
        if ($tenant) {
            $tenant->update([
                'status' => 0,
                'rent_end_date' => $completionDate,
            ]);
        }
        $asset->save();
        $asset->rentals()->delete();
        $asset->rentalPaymentsHistories()->update(['status' => 0]);

        return ServiceResponse::jsonNotification('Rent updated successfully', 200, []);
    }
}
