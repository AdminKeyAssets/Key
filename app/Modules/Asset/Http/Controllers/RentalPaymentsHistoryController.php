<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\RentsPaymentsExport;
use App\Modules\Admin\Exports\RentsScheduleExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Helpers\UpdateRentalPaymentsHelper;
use App\Modules\Asset\Http\Requests\LeaseRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
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
        $this->baseData['allData'] = RentalPaymentsHistory::where('asset_id', $assetId)->orderByDesc('id')->paginate(25);
        $this->baseData['assetId'] = $assetId;
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param $assetId
     * @return Application|Factory|View
     */
    public function create($assetId)
    {
        $this->baseData['assetId'] = $assetId;
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
            $this->baseData['assets'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())->get();
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
        $path = null;

        if (isset($request->id)) {
            $paymentHistory = RentalPaymentsHistory::where('id', $request->id)->first();
            $oldAmount = $paymentHistory->amount;

            if ($request->hasFile('attachment')) {
                if ($paymentHistory->attachment && Storage::disk('public')->exists($paymentHistory->attachment)) {
                    Storage::disk('public')->delete($paymentHistory->attachment);
                }

                $file = $request->file('attachment');
                $originalFileName = $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName,'public');
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

            $this->paymentsHelper->recalculateRentalPaymentsAfterEdit($paymentHistory->asset, $oldAmount, $request->amount);
        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName,'public');
                $path = Storage::url($path);
            }

            $paymentHistory = RentalPaymentsHistory::create([
                'asset_id' => $assetId,
                'date' => $request->date,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path
            ]);

            $this->paymentsHelper->updateRentalPayments($paymentHistory->asset, $paymentHistory->amount);
        }
        $this->baseData['item'] = $paymentHistory;

        return ServiceResponse::jsonNotification('Payment Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.rental.create_data');

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
            $this->baseData['routes']['create_form_data'] = route('asset.rental.create_data');

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
    public function destroy(Request $request, $assetId)
    {
        try {
            $paymentHistory = RentalPaymentsHistory::findOrFail($request->get('id'));
            $amount = $paymentHistory->amount;
            $asset = $paymentHistory->asset;
            $paymentHistory->delete();

            $this->paymentsHelper->recalculateRentalPaymentsAfterDeletion($asset, $amount);
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
}
