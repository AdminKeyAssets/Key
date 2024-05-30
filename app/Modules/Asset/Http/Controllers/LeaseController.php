<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Http\Requests\LeaseRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Lease;
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
     * @return Application|Factory|View
     */
    public function index(Request $request, $assetId)
    {
        $this->baseData['allData'] = Lease::where('asset_id', $assetId)->orderByDesc('id')->paginate(25);
        $this->baseData['assetId'] = $assetId;
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param Request $request
     * @return Application|Factory|RedirectResponse|Redirector|View
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'SalesManager'];
        $assets = null;
        if (in_array($user->getRolesNameAttribute(), $managers)) {
            $assets = Asset::where('admin_id', $user->getAuthIdentifier())->get();
        } else if ($user->getRolesNameAttribute() === 'Investor') {
            $assets = Asset::where('investor_id', $user->getAuthIdentifier())->get();
        } else {
            return redirect(route('asset.index'));
        }

        $assetIds = [];
        foreach ($assets as $asset) {
            $assetIds[] = $asset->id;
        }

        $this->baseData['allData'] = Lease::whereIn('id', $assetIds)->orderByDesc('id')->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param $assetId
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
                'create' => route('asset.lease.create'),
                'create_data' => route('asset.lease.create_data'),
                'save' => route('asset.lease.store'),
                'edit' => route('asset.lease.edit', []),
            ];
            $this->baseData['assets'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())->get();
            if ($request->get('id')) {
                $lease = Lease::findOrFail($request->get('id'));

                $this->baseData['item'] = $lease;
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
    public function store(LeaseRequest $request)
    {
        $updatedPayment = Lease::updateOrCreate(['id' => $request->id], [
            'asset_id' => $request->asset_id,
            'price' => $request->price,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        $this->baseData['item'] = $updatedPayment;

        return ServiceResponse::jsonNotification('Payment Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.lease.create_data');

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
            $this->baseData['routes']['create_form_data'] = route('asset.lease.create_data');

            $this->baseData['id'] = $id;
            $rental = Lease::find($id);
            $this->baseData['income'] = $this->calculateRentalCost($rental->price, $rental->date_from, $rental->date_to);

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
            Lease::findOrFail($request->get('id'))->delete();
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

    public function calculateRentalCost($price, $dateFrom, $dateTo)
    {
        $dateFrom = Carbon::parse($dateFrom);
        $dateTo = Carbon::parse($dateTo);
        $days = $dateTo->diffInDays($dateFrom);
        $costPerDay = $price / 30;

        return $days * $costPerDay;
    }
}
