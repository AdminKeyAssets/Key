<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
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

        return view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName.'.index', $this->baseData);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        try {
            $this->baseData['routes']['create_form_data'] = SlugHelper::getRoutes()['create_data'];
        } catch (\Exception $ex) {
            return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }

        return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws GetCreateDataException
     */
    public function createData(Request $request)
    {
        try {
            $this->baseData['routes'] = SlugHelper::getRoutes();
            if ($request->get('id')) {
                $this->baseData['item'] = (new CreateSlugResource(Slug::findOrFail($request->get('id'))->load(['slugable'])))->toArray($request);
            }
        } catch (\Exception $ex) {
            throw new GetCreateDataException($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200,  $this->baseData);
    }

    public function store()
    {

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200,  $this->baseData );
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('admincreate_data');

            $this->baseData['id'] = $id;

        } catch (\Exception $ex) {
            return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::error($ex->getMessage()));
        }

        return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::success($this->baseData));
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
            if(!$slug->slugable) {
                $slug->delete();
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200,  $this->baseData );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        try {
            Slug::findOrFail($request->get('id'))->update(['status' => $request->get('status')]);
        } catch (\Exception $ex) {
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['update_status_successfully'], 200,  $this->baseData );
    }

}
