<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Http\Requests\InvestmentRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Investment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mockery\Exception;

class InvestmentController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'investment';

    public $baseName = 'investment.';

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
        $this->baseData['allData'] = Investment::where('asset_id', $assetId)->orderByDesc('id')->paginate(25);
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
                'create' => route('asset.investment.create', $assetId),
                'create_data' => route('asset.investment.create_data', $assetId),
                'save' => route('asset.investment.store', $assetId),
                'edit' => route('asset.investment.edit', $assetId, []),
            ];
            $this->baseData['assets'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())->get();
            if ($request->get('id')) {
                $rental = Investment::findOrFail($request->get('id'));

                $this->baseData['item'] = $rental;
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param InvestmentRequest $request
     * @param $assetId
     * @return JsonResponse
     */
    public function store(InvestmentRequest $request, $assetId)
    {
        $path = null;

        if (isset($request->id)) {
            $investment = Investment::where('id', $request->id)->first();

            if ($request->hasFile('attachment')) {
                if ($investment->attachment && Storage::disk('public')->exists($investment->attachment)) {
                    Storage::disk('public')->delete($investment->attachment);
                }

                $file = $request->file('attachment');
                $originalFileName = $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);

            } else if ($request->input('attachment') === null) {
                if ($investment->attachment && Storage::disk('public')->exists($investment->attachment)) {
                    Storage::disk('public')->delete($investment->attachment);
                }
            } else {
                $path = $investment->attachment;
            }

            $investment->update([
                'asset_id' => $assetId,
                'date' => $request->date,
                'status' => $request->status,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path
            ]);

        } else {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }

            $investment = Investment::create([
                'asset_id' => $assetId,
                'date' => $request->date,
                'status' => $request->status,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'attachment' => $path,
            ]);

        }
        $this->baseData['item'] = $investment;

        return ServiceResponse::jsonNotification('Investment Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.investment.create_data');

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
    public function view($assetId, $id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.investment.create_data', $assetId);

            $this->baseData['assetId'] = $assetId;
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
            Investment::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }
}
