<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Asset\Http\Requests\PaymentRequest;
use App\Modules\Asset\Models\Payment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mockery\Exception;

class PaymentController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'payment';

    public $baseName = 'payments.';

    public function __construct()
    {
        parent::__construct();
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
        $this->baseData['allData'] = Payment::orderByDesc('id')->paginate(25);
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
     * @param $assetId
     * @return JsonResponse
     */
    public function createData(Request $request, $assetId)
    {
        try {
            $this->baseData['routes'] = [
                'create' => route('asset.payments.create', $assetId),
                'create_data' => route('asset.payments.create_data', $assetId),
                'save' => route('asset.payments.store', $assetId),
                'edit' => route('asset.payments.edit', $assetId, []),
            ];
            if ($request->get('id')) {
                $payment = Payment::findOrFail($request->get('id'));

                $this->baseData['item'] = $payment;
                $this->baseData['item']['attachment'] = $payment->attachment ? Storage::url($payment->attachment) : null;
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

        if (isset($request->id)) {
            $payment = Payment::where('id', $request->id)->first();
            if ($request->hasFile('attachment')) {
                if ($payment->attachment && Storage::disk('public')->exists($payment->attachment)) {
                    Storage::disk('public')->delete($payment->attachment);
                }

                $file = $request->file('attachment');
                $path = $file->store('uploads', 'public');

            } else if ($request->input('attachment') === null) {
                if ($payment->attachment && Storage::disk('public')->exists($payment->attachment)) {
                    Storage::disk('public')->delete($payment->attachment);
                }
                $path = null;
            }

        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('uploads', 'public');

            }
        }

        $updatedPayment = Payment::updateOrCreate(['id' => $request->id], [
            'asset_id' => $assetId,
            'month' => $request->month,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'status' => $request->status ? 1 : 0,
            'attachment' => $path
        ]);

        $this->baseData['item'] = $updatedPayment;

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
            $this->baseData['routes']['create_form_data'] = route('asset.payments.create_data', $assetId);

            $this->baseData['assetId'] = $assetId;
            $this->baseData['id'] = $id;

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
            $this->baseData['routes']['create_form_data'] = route('asset.payments.create_data', $assetId);

            $this->baseData['assetId'] = $assetId;
            $this->baseData['id'] = $id;
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
    public function destroy(Request $request, $assetId)
    {
        try {
            Payment::findOrFail($request->get('id'))->delete();


        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

}
