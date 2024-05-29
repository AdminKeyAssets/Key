<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Asset\Http\Requests\AssetRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\AssetAttachment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
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
    public function index(Request $request)
    {
        $this->baseData['allData'] = Asset::orderByDesc('id')->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function myassets(Request $request)
    {
        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'SalesManager'];
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        if(in_array($user->getRolesNameAttribute(), $managers)){
            $this->baseData['allData'] = Asset::where('admin_id', $userId)->orderByDesc('id')->paginate(25);
        }else{
            $this->baseData['allData'] = Asset::where('investor_id', $userId)->orderByDesc('id')->paginate(25);
        }


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
                'create' => route($this->baseName . 'create'),
                'create_data' => route($this->baseName . 'create_data'),
                'save' => route($this->baseName . 'store'),
                'edit' => route($this->baseName . 'edit', []),
            ];

            if ($request->get('id')) {
                $asset = Asset::findOrFail($request->get('id'));

                $salesManager = null;
                $nextPayment = null;

                if ($asset->investor_id) {
                    $investor = Admin::where('id', $asset->investor_id)->first();
                    if($investor->parent_id){
                        $salesManager = Admin::where('id', $investor->parent_id)->first();
                    }
                }

                if($asset->payments){
                    $nextPayment = $asset->payments->where('status', 0)->first();
                }

                $this->baseData['item'] = $asset;
                $this->baseData['item']['extraDetails'] = $asset->informations;
                $this->baseData['item']['payments'] = $asset->payments;
                $this->baseData['salesManager'] = $salesManager;
                $this->baseData['nextPayment'] = $nextPayment;


                $files = [];
                if ($asset->attachments) {
                    foreach ($asset->attachments as $item) {
                        $files[] = [
                            'name' => $item->name,
                            'path' => Storage::url($item->path),
                            'type' => substr($item->type, 0, 5) == 'image' ? 'image' : null
                        ];
                    }
                }
                $this->baseData['item']['files'] = $files;
                if ($asset->icon) {
                    $this->baseData['item']['icon'] = Storage::url($asset->icon);
                }


            }

            $this->baseData['investors'] = Admin::role(['Investor'])->get(['name', 'id']);

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    /**
     * @param AssetRequest $request
     * @return JsonResponse
     */
    public function store(AssetRequest $request)
    {
        $path = null;
        if (isset($request->id)) {
            $asset = Asset::where('id', $request->id)->first();
            if ($request->hasFile('icon')) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }

                $file = $request->file('icon');
                $path = $file->store('uploads', 'public');

            } else if ($request->input('icon') === null) {
                if ($asset->icon && Storage::disk('public')->exists($asset->icon)) {
                    Storage::disk('public')->delete($asset->icon);
                }
                $path = null;
            }

        } else {
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $path = $file->store('uploads', 'public');

            }
        }

        $asset = Asset::updateOrCreate(['id' => $request->id], [
            'name' => $request->name,
            'address' => $request->address,
            'cadastral_number' => $request->cadastral_number,
            'investor_id' => $request->investor_id,
            'city' => $request->city,
            'delivery_date' => $request->delivery_date,
            'area' => $request->area,
            'total_price' => $request->total_price,
            'icon' => $path,
            'admin_id' => Auth::user()->getAuthIdentifier(),
            'currency' => $request->currency
        ]);


        if ($request->has('attachmentsToRemove')) {
            $attachmentsToRemove = json_decode($request->attachmentsToRemove, true);
            foreach ($attachmentsToRemove as $attachmentId) {
                $attachment = AssetAttachment::find($attachmentId);
                if ($attachment) {
                    Storage::disk('public')->delete($attachment->path);
                    $attachment->delete();
                }
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('uploads', 'public');
                AssetAttachment::create([
                    'asset_id' => $asset->id,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType()
                ]);
            }
        }

        $asset->informations()->delete();
        if (!empty($request->extraDetails)) {
            foreach (json_decode($request->extraDetails) as $info) {
                $asset->informations()->create([
                    'key' => $info->key,
                    'value' => $info->value,
                ]);
            }
        }
        return ServiceResponse::jsonNotification('Asset Added successfully', 200, $this->baseData);
    }

    /**
     * @param $id
     * @return Application|Factory|View
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

    /**
     * @param $id
     * @return Application|Factory|View
     */
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
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            Asset::findOrFail($request->get('id'))->delete();

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

}
