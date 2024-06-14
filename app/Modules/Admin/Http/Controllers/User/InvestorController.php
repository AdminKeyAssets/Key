<?php

namespace App\Modules\Admin\Http\Controllers\User;

use App\Modules\Admin\Exports\InvestorsExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\User\SaveUserRequest;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvestorController extends BaseController
{


    /**
     * @var string
     */
    public $viewFolderName = 'investor';

    /**
     * @var IRoleRepository
     */
    protected $roleRepository;

    /**
     * @var IPermissionRepository
     */
    protected $permissionRepository;


    /**
     * RoleController constructor.
     * @param IRoleRepository $roleRepository
     * @param IPermissionRepository $permissionRepository
     */
    public function __construct
    (
        IRoleRepository       $roleRepository,
        IPermissionRepository $permissionRepository
    )
    {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->baseData['moduleKey'] = 'investor';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $query = Investor::query();
        if ($request->citizenship) {
            $query->where('citizenship', '=', $request->input('citizenship'));
        }
        if ($request->assets) {
            $assetsCount = $request->assets;
            $query->whereHas('assets', function ($query) use ($assetsCount) {
                if ($assetsCount > 0) {
                    $query->havingRaw('COUNT(*) = ?', [$assetsCount]);
                }
            });
        }

        if($request->create_date){
            $createdDates = explode(',', $request->create_date);
            $query->where('created_at', '>=', $createdDates[0]);
            $query->where('created_at', '<=', $createdDates[1]);
        }

        if($request->manager){
            $managerName = $request->manager;
            $query->whereHas('admin', function ($query) use ($managerName){
                $query->whereHas('roles', function ($roleQuery) use ($managerName){
                    $roleQuery->where('name', '=', $managerName);
                });
            });
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
            $this->baseData['investor'] = [];

            $this->baseData['routes'] = [
                'create_form_data' => route('admin.investor.create_form_data'),
                'create' => route('admin.investor.create_form'),
                'save' => route('admin.investor.save'),
                'delete' => route('admin.investor.delete')
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
            $this->baseData['routes']['create_form_data'] = route('admin.investor.create_form_data');

            $this->baseData['id'] = $id;

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('admin.investor.create_form_data'),
                'create' => route('admin.investor.create_form'),
                'save' => route('admin.investor.save'),
                'delete' => route('admin.investor.delete')
            ];

            if ($request->get('id')) {
                $investor = Investor::findOrFail($request->get('id'));
                $this->baseData['item'] = $investor;
            }
            $this->baseData['countries'] = Country::get('country');
            $this->baseData['prefixes'] = Country::groupBy('prefix')->get('prefix');

        } catch (\Exception $ex) {
            Log::error('Error during roles index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200, $this->baseData);
    }

    /**
     * @param SaveUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveUserRequest $request)
    {
        try {
            $passport = null;
            $profilePicture = null;

            if (isset($request->id)) {
                $investor = Investor::where('id', $request->id)->first();
                if ($request->hasFile('profile_picture')) {
                    if ($investor->profile_picture && Storage::disk('public')->exists($investor->profile_picture)) {
                        Storage::disk('public')->delete($investor->profile_picture);
                    }

                    $profilePictureFile = $request->file('profile_picture');
                    $profilePicture = $profilePictureFile->store('uploads', 'public');

                } else if ($request->input('profile_picture') === null) {
                    if ($investor->profile_picture && Storage::disk('public')->exists($investor->profile_picture)) {
                        Storage::disk('public')->delete($investor->profile_picture);
                    }
                }

                if ($request->hasFile('passport')) {
                    if ($investor->passport && Storage::disk('public')->exists($investor->passport)) {
                        Storage::disk('public')->delete($investor->profile_picture);
                    }

                    $passportFile = $request->file('passport');
                    $passport = $passportFile->store('uploads', 'public');

                } else if ($request->input('passport') === null) {
                    if ($investor->passport && Storage::disk('public')->exists($investor->passport)) {
                        Storage::disk('public')->delete($investor->passport);
                    }
                }

            } else {
                if ($request->hasFile('profile_picture')) {
                    $profilePictureFile = $request->file('profile_picture');
                    $profilePicture = $profilePictureFile->store('uploads', 'public');
                }
                if ($request->hasFile('passport')) {
                    $passportFile = $request->file('passport');
                    $passport = $passportFile->store('uploads', 'public');
                }
            }


            Investor::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'email' => $request->email,
                    'prefix' => $request->prefix,
                    'phone' => $request->phone,
                    'pid' => $request->pid,
                    'citizenship' => $request->citizenship,
                    'address' => $request->address,
                    'password' => $request->password,
                    'passport' => !is_null($passport) ? Storage::url($passport) : null,
                    'profile_picture' => !is_null($profilePicture) ? Storage::url($profilePicture) : null,
                    'admin_id' => Auth::user()->getAuthIdentifier()
                ]
            );
        } catch (\Exception $ex) {
            Log::error('Error during roles index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification("Investor Saved Successfully", 200, $this->baseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            if (auth()->user()->id == $request->get('id')) {
                throw new \Exception('You are not allowed to delete this user!');
            }

            $investor = Investor::find($request->get('id'));
            if ($investor->assets) {
                throw new \Exception('You are not allowed to delete user, while having assets attached on it!!');
            }
            $investor->delete();

        } catch (\Exception $ex) {
            Log::error('Error during delete user', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['delete_successfully'], 200, $this->baseData);

    }

    public function export(Request $request)
    {
        $filters = $request->only(['email', 'phone']);
        return Excel::download(new InvestorsExport($filters), 'investors.xlsx');
    }

    public function filterOptions()
    {
        $this->baseData['countries'] = Country::get('country');
        $this->baseData['managers'] = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'like', '%manager%');
        })->get();

        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200, $this->baseData);
    }
}
