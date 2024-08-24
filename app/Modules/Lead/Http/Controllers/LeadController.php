<?php

namespace App\Modules\Lead\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Lead\Http\Requests\LeadRequest;
use App\Modules\Lead\Models\Lead;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery\Exception;

class LeadController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'lead::';
    /**
     * @var string
     */
    public $viewFolderName = 'lead';

    public $baseName = 'lead.';

    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'lead';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $this->baseData['allData'] = Lead::orderByDesc('id')->paginate(25);
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
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
                'create' => route('lead.lead.create'),
                'create_data' => route('lead.lead.create_data'),
                'save' => route('lead.lead.store'),
                'edit' => route('lead.lead.edit', []),
            ];

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param LeadRequest $request
     * @return JsonResponse
     */
    public function store(LeadRequest $request)
    {
        $lead = Lead::updateOrCreate(['email' => $request->email],
            [
                'name' => $request->name,
                'surname' => $request->surname,
                'phone' => $request->phone
            ]);
        $this->baseData['item'] = $lead;

        return ServiceResponse::jsonNotification('Investment Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('lead.lead.create_data');

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
            $this->baseData['routes']['create_form_data'] = route('lead.lead.create_data');

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
    public function destroy(Request $request)
    {
        try {
            Lead::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }
}
