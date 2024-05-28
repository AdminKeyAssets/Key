<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Http\Requests\LeaseRequest;
use App\Modules\Asset\Models\Lease;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mockery\Exception;

class LeaseController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'lease';

    public $baseName = 'lease.';

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
        $this->baseData['allData'] = Lease::orderByDesc('id')->paginate(25);
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
                'create' => route('asset.lease.create', $assetId),
                'create_data' => route('asset.lease.create_data', $assetId),
                'save' => route('asset.lease.store', $assetId),
                'edit' => route('asset.lease.edit', $assetId, []),
            ];
            if ($request->get('id')) {
                $payment = Lease::findOrFail($request->get('id'));

                $this->baseData['item'] = $payment;
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param LeaseRequest $request
     * @param $assetId
     * @return JsonResponse
     */
    public function store(LeaseRequest $request, $assetId)
    {
        $updatedPayment = Lease::updateOrCreate(['id' => $request->id], [
            'asset_id' => $assetId,
            'price' => $request->price,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
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
            $this->baseData['routes']['create_form_data'] = route('asset.lease.create_data', $assetId);

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
            $this->baseData['routes']['create_form_data'] = route('asset.lease.create_data', $assetId);

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
            Lease::findOrFail($request->get('id'))->delete();
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

}
