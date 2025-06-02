<?php

namespace App\Modules\Admin\Http\Controllers\User;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\Developer\SaveDeveloperRequest;
use App\Modules\Admin\Models\DeveloperAgreement;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Developer;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\DeveloperAsset;
use App\Utilities\ServiceResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DeveloperController extends BaseController
{
    /**
     * @var string
     */
    public $viewFolderName = 'developer';

    /**
     * DeveloperController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'developer';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $query = Developer::query();

        if ($request->search && $request->search != 'all') {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('representative', 'like', '%' . $request->search . '%')
                ->orWhere('id_code', 'like', '%' . $request->search . '%');
        }

        $this->baseData['allData'] = $query->paginate();

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('admin.developer.create_form_data'),
                'create' => route('admin.developer.create_form'),
                'save' => route('admin.developer.save'),
                'delete' => route('admin.developer.delete')
            ];

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::success($this->baseData));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('admin.developer.create_form_data');
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
            $this->baseData['routes']['create_form_data'] = route('admin.developer.create_form_data');
            $this->baseData['id'] = $id;

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('admin.developer.create_form_data'),
                'create' => route('admin.developer.create_form'),
                'save' => route('admin.developer.save'),
                'delete' => route('admin.developer.delete')
            ];

            if ($request->get('id')) {
                $developer = Developer::findOrFail($request->get('id'));
                $this->baseData['item'] = $developer;
                $this->baseData['item']['agreements'] = $developer->agreements()->get();
                $this->baseData['item']['assets'] = $developer->assets()->pluck('asset_name')->toArray();
            }
            $this->baseData['assets'] = Asset::where('developer_access', 1)
                ->groupBy('project_name')
                ->orderBy('project_name')
                ->pluck('project_name')
                ->toArray();
        } catch (\Exception $ex) {
            Log::error('Error during developer create data', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification(__('Developer data loaded successfully'), 200, $this->baseData);
    }

    /**
     * @param SaveDeveloperRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveDeveloperRequest $request)
    {
        try {
            $logo = null;
            $stamp = null;
            $signature = null;

            if (isset($request->id)) {
                $developer = Developer::where('id', $request->id)->first();

                if ($request->hasFile('logo')) {
                    if ($developer->logo && Storage::disk('public')->exists($developer->logo)) {
                        Storage::disk('public')->delete($developer->logo);
                    }

                    $logoFile = $request->file('logo');
                    $logo = $logoFile->store('uploads', 'public');
                    $logo = Storage::url($logo);
                } else if ($request->input('logo') === null) {
                    if ($developer->logo && Storage::disk('public')->exists($developer->logo)) {
                        Storage::disk('public')->delete($developer->logo);
                    }
                } else {
                    $logo = $request->input('logo');
                }

                if ($request->hasFile('stamp')) {
                    if ($developer->stamp && Storage::disk('public')->exists($developer->stamp)) {
                        Storage::disk('public')->delete($developer->stamp);
                    }
                    $stampFile = $request->file('stamp');
                    $stamp = $stampFile->store('uploads', 'public');
                    $stamp = Storage::url($stamp);
                } else if ($request->input('stamp') === null) {
                    if ($developer->stamp && Storage::disk('public')->exists($developer->stamp)) {
                        Storage::disk('public')->delete($developer->stamp);
                    }
                } else {
                    $stamp = $request->input('stamp');
                }

                if ($request->hasFile('signature')) {
                    if ($developer->signature && Storage::disk('public')->exists($developer->signature)) {
                        Storage::disk('public')->delete($developer->signature);
                    }
                    $signatureFile = $request->file('signature');
                    $signature = $signatureFile->store('uploads', 'public');
                    $signature = Storage::url($signature);
                } else if ($request->input('signature') === null) {
                    if ($developer->signature && Storage::disk('public')->exists($developer->signature)) {
                        Storage::disk('public')->delete($developer->signature);
                    }
                } else {
                    $signature = $request->input('signature');
                }

            } else {
                if ($request->hasFile('logo')) {
                    $logoFile = $request->file('logo');
                    $logo = $logoFile->store('uploads', 'public');
                    $logo = Storage::url($logo);
                }
                if ($request->hasFile('stamp')) {
                    $stampFile = $request->file('stamp');
                    $stamp = $stampFile->store('uploads', 'public');
                    $stamp = Storage::url($stamp);
                }
                if ($request->hasFile('signature')) {
                    $signatureFile = $request->file('signature');
                    $signature = $signatureFile->store('uploads', 'public');
                    $signature = Storage::url($signature);
                }
            }

            $data = [
                'name' => $request->name,
                'id_code' => $request->id_code,
                'representative' => $request->representative,
                'tel' => $request->tel,
                'representative_position' => $request->representative_position,
                'username' => $request->username,
                'logo' => $logo,
                'stamp' => $stamp,
                'signature' => $signature,
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }
            if ($request->id) {
                $developer = Developer::where('id', $request->id)->first();
                $developer->update($data);
            } else {
                $developer = Developer::create($data);
            }


            $developer->agreements()->delete();

            if (!empty($request->agreements)) {
                foreach ($request->agreements as $agreement) {
                    $agreementAttachmentPath = null;
//dd($agreement);
                    if (isset($agreement['path']) && gettype($agreement['path']) == 'object') {
                        $agreementAttachmentFile = $agreement['path'];
                        $originalFileName = time() . '_' . $agreementAttachmentFile->getClientOriginalName();
                        $agreementAttachmentPath = $agreementAttachmentFile->storeAs('uploads', $originalFileName, 'public');
                        $agreementAttachmentPath = Storage::url($agreementAttachmentPath);
                    } else {
                        $agreementAttachmentPath = $agreement['path'] ?? null;
                    }
//                    dd($agreement['name'], $agreementAttachmentPath);
                    DeveloperAgreement::create([
                        'name' => $agreement['name'],
                        'path' => $agreementAttachmentPath,
                        'developer_id' => $developer->id
                    ]);

                }
            }

            $developer->assets()->delete();

            if(!empty($request->assets)) {
                foreach ($request->assets as $asset) {
                   $developerAsset = DeveloperAsset::create([
                        'asset_name' => $asset,
                        'developer_id' => $developer->id
                    ]);
                }
            }

        } catch (\Exception $ex) {
            Log::error('Error during developer save', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification("Developer Saved Successfully", 200, $this->baseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $developer = Developer::where('id', $request->id)->first();

            if (!$developer) {
                throw new \Exception('Developer not found');
            }

            // Delete associated files
            if ($developer->logo && Storage::disk('public')->exists($developer->logo)) {
                Storage::disk('public')->delete($developer->logo);
            }

            if ($developer->stamp && Storage::disk('public')->exists($developer->stamp)) {
                Storage::disk('public')->delete($developer->stamp);
            }

            if ($developer->signature && Storage::disk('public')->exists($developer->signature)) {
                Storage::disk('public')->delete($developer->signature);
            }

            if ($developer->service_agreement && Storage::disk('public')->exists($developer->service_agreement)) {
                Storage::disk('public')->delete($developer->service_agreement);
            }

            $developer->delete();

        } catch (\Exception $ex) {
            Log::error('Error during delete developer', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification('Developer Deleted Successfully', 200, $this->baseData);
    }

    public function filterOptions()
    {
        $this->baseData['assets'] = Asset::orderBy('project_name')
            ->groupBy('project_name')
            ->pluck('project_name')
            ->toArray();

        return ServiceResponse::jsonNotification(__('Asset List'), 200, $this->baseData);
    }

    public function developerAssetsInvestors(Request $request)
    {
        $assetName = $request->asset;
        $developerId = $request->developer_id;
        $developer = Developer::where('id', $developerId)->first();
        $assets = Asset::where('developer_access', 1)
            ->where('project_name', $assetName)
            ->get();
        $investorsArray = [];
        foreach ($assets as $asset) {
            $investors = $asset->investors;
            foreach ($investors as $investor) {
                $investorName = $investor->name . ' ' . $investor->surname;
                if (!in_array($investorName, $investorsArray))
                    $investorsArray[] = $investorName;
            }
        }

        $this->baseData['investors'] = $investorsArray;

        return ServiceResponse::jsonNotification(__('Investor List'), 200, $this->baseData);
    }

    public function updateAssets(Request $request)
    {
        $developer = Developer::find($request->developer_id);

        $developer->assets()->delete();
        foreach ($request->assets as $asset) {
            $developer->assets()->create(['asset_name' => $asset]);
        }

        $this->baseData['assets'] = $developer->assets()
            ->orderBy('asset_name')
            ->pluck('asset_name')
            ->toArray();

        return ServiceResponse::jsonNotification(__('Assets updated successfully'), 200, $this->baseData);

    }

    public function developerManagers()
    {
        $user = \auth()->user();
        $developerAssetNames = $user->assets()->pluck('asset_name')->toArray();
        $this->baseData['allData'] = Admin::whereIn('id', Asset::whereIn('project_name', $developerAssetNames)->pluck('admin_id')->toArray())->paginate(25);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.managers', $this->baseData);
    }
}
