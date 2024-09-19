<?php

namespace App\Modules\Lead\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
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

class SaleController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'lead::';
    /**
     * @var string
     */
    public $viewFolderName = 'sale';

    public $baseName = 'sale.';

    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'sale';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $this->baseData['allData'] = Sale::orderByDesc('agreement_date')->paginate(25);
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
                'create' => route('sale.create'),
                'create_data' => route('sale.create_data'),
                'save' => route('sale.store'),
                'edit' => route('sale.edit', []),
            ];
            if ($request->get('id')) {
                $sale = Sale::findOrFail($request->get('id'));
                $this->baseData['item'] = $sale;
            }

            $this->baseData['investors'] = Investor::get()->map(function ($investor) {
                return ['value' => $investor->name . ' ' . $investor->surname];
            });

            $this->baseData['projects'] = Asset::get()->map(function ($project) {
                return ['value' => $project->project_name];
            });

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param SaleRequest $request
     * @return JsonResponse
     */
    public function store(SaleRequest $request)
    {
        $attachment = null;

        if ($request->id) {
            $sale = Sale::where('id', $request->id)->first();

            if ($request->hasFile('attachment')) {
                if ($sale->attachment && Storage::disk('public')->exists($sale->attachment)) {
                    Storage::disk('public')->delete($sale->attachment);
                }

                $attachmentFile = $request->file('attachment');
                $attachment = $attachmentFile->store('uploads', 'public');
                $attachment = Storage::url($attachment);

            } else if ($request->input('attachment') === null) {
                if ($sale->attachment && Storage::disk('public')->exists($sale->attachment)) {
                    Storage::disk('public')->delete($sale->attachment);
                }
            } else {
                $attachment = $request->input('attachment');
            }


            $sale->update([
                'project' => $request->project,
                'investor' => $request->investor,
                'type' => $request->type,
                'size' => $request->size,
                'price' => $request->price,
                'total_price' => $request->total_price,
                'agreement_status' => $request->agreement_status,
                'agreement_date' => $request->agreement_date,
                'down_payment' => $request->down_payment,
                'period' => $request->period,
                'marketing_channel' => $request->marketing_channel,
                'attachment' => $attachment,
                'commission' => $request->commission,
                'complete' => $request->complete === 'true',
            ]);
        } else {

            if ($request->hasFile('attachment')) {
                $attachmentFile = $request->file('attachment');
                $attachment = $attachmentFile->store('uploads', 'public');
                $attachment = Storage::url($attachment);
            }

            $sale = Sale::Create([
                'project' => $request->project,
                'investor' => $request->investor,
                'type' => $request->type,
                'size' => $request->size,
                'price' => $request->price,
                'total_price' => $request->total_price,
                'agreement_status' => $request->agreement_status,
                'agreement_date' => $request->agreement_date,
                'down_payment' => $request->down_payment,
                'period' => $request->period,
                'marketing_channel' => $request->marketing_channel,
                'attachment' => $attachment,
                'commission' => $request->commission,
                'complete' => $request->complete === 'true',
            ]);
        }

        $this->baseData['item'] = $sale;

        return ServiceResponse::jsonNotification('Sale Added successfully', 200, $this->baseData);
    }


    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('sale.create_data');

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
            $this->baseData['routes']['create_form_data'] = route('sale.create_data');

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
            Sale::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

}
