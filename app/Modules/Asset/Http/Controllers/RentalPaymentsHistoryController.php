<?php

namespace App\Modules\Asset\Http\Controllers;

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
            if ($request->hasFile('attachment')) {
                if ($paymentHistory->attachment && Storage::disk('public')->exists($paymentHistory->attachment)) {
                    Storage::disk('public')->delete($paymentHistory->attachment);
                }

                $file = $request->file('attachment');
                $path = $file->store('uploads', 'public');
                $path = Storage::url($path);

            } else if ($request->input('attachment') === null) {
                if ($paymentHistory->attachment && Storage::disk('public')->exists($paymentHistory->attachment)) {
                    Storage::disk('public')->delete($paymentHistory->attachment);
                }
            }

        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('uploads', 'public');
                $path = Storage::url($path);
            }
        }

        $updatedPaymentsHistory = RentalPaymentsHistory::updateOrCreate(['id' => $request->id], [
            'asset_id' => $assetId,
            'date' => $request->date,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'attachment' => $path
        ]);

        $this->paymentsHelper->updateRentalPayments($updatedPaymentsHistory->asset, $updatedPaymentsHistory->amount);

        $this->baseData['item'] = $updatedPaymentsHistory;

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
            RentalPaymentsHistory::findOrFail($request->get('id'))->delete();
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
}
