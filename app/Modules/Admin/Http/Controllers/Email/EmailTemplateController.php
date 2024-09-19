<?php

namespace App\Modules\Admin\Http\Controllers\Email;

use App\Modules\Admin\Exports\SalesExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\EmailTemplateRequest;
use App\Modules\Admin\Models\EmailTemplate;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Lead\Http\Requests\SaleRequest;
use App\Modules\Lead\Models\Sale;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mockery\Exception;
use Maatwebsite\Excel\Facades\Excel;

class EmailTemplateController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'admin::';
    /**
     * @var string
     */
    public $viewFolderName = 'template';

    public $baseName = 'template.';

    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'template';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $this->baseData['allData'] = EmailTemplate::paginate(25);
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @return JsonResponse
     */
    public function filter()
    {
        $this->baseData['templates'] = EmailTemplate::get();
        return ServiceResponse::jsonNotification(__(''), 200, $this->baseData);
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
                'create' => route('template.create'),
                'create_data' => route('template.create_data'),
                'save' => route('template.store'),
                'list' => route('template.index'),
                'edit' => route('template.edit', []),
            ];
            if ($request->get('id')) {
                $emailTemplate = EmailTemplate::findOrFail($request->get('id'));
                $this->baseData['item'] = $emailTemplate;
            }


        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param EmailTemplateRequest $request
     * @return JsonResponse
     */
    public function store(EmailTemplateRequest $request)
    {
        if ($request->id) {
            $emailTemplate = EmailTemplate::where('id', $request->id)->first();

            $emailTemplate->update([
                'name' => $request->name,
                'body' => $request->body,
            ]);
        } else {

            $emailTemplate = EmailTemplate::Create([
                'name' => $request->name,
                'body' => $request->body,
            ]);
        }

        $this->baseData['item'] = $emailTemplate;

        return ServiceResponse::jsonNotification('Email Template Saved successfully', 200, $this->baseData);
    }


    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('template.create_data');

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
            $this->baseData['routes']['create_form_data'] = route('template.create_data');

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
            EmailTemplate::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }
}
