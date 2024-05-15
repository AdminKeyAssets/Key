<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Asset\Http\Requests\AssetRequest;
use App\Modules\Asset\Models\Asset;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
     * SlugController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->successCreateText = $this->moduleTitle . $this->successCreateText;
        $this->successUpdateText = $this->moduleTitle . $this->successUpdateText;
        $this->successDeleteText = $this->moduleTitle . $this->successDeleteText;
        $this->baseData['moduleKey'] = 'asset';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->baseData['allData'] = Asset::orderByDesc('id')->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    public function myassets(Request $request)
    {
        $userId = auth()->user()->getAuthIdentifier();
        $this->baseData['allData'] = Asset::where('investor_id', $userId)->orderByDesc('id')->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', $this->baseData);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
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

                $this->baseData['item'] = $asset;
                $this->baseData['item']['extraDetails'] = $asset->informations;

            }

            $this->baseData['investors'] = Admin::role(['Investor'])->get(['name', 'id']);

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    public function store(AssetRequest $request)
    {
        $asset = Asset::updateOrCreate(['id' => $request->id], [
            'name' => $request->name,
            'address' => $request->address,
            'cadastral_number' => $request->cadastral_number,
            'investor_id' => $request->investor_id,
            'city' => $request->city,
            'delivery_date' => $request->delivery_date,
            'area' => $request->area,
            'total_price' => $request->total_price,

        ]);
        $asset->informations()->delete();

        if (!empty($request->extraDetails)) {
            foreach ($request->extraDetails as $info) {
                $asset->informations()->create([
                    'key' => $info["key"],
                    'value' => $info["value"],
                ]);
            }
        }
        return ServiceResponse::jsonNotification('Asset Added successfully', 200, $this->baseData);
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws DeleteStaticPageException
     */
    public function destroy(Request $request)
    {
        try {
            $slug = Asset::findOrFail($request->get('id'));
            if (!$slug->slugable) {
                $slug->delete();
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

}
