<?php

namespace App\Modules\Admin\Http\Controllers\User;

use App\Models\Role;
use App\Modules\Admin\Exports\AdminsExport;
use App\Modules\Admin\Helper\TextHelper;
use App\Modules\Admin\Helper\UserHelper;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\User\SaveUserRequest;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends BaseController
{


    /**
     * @var string
     */
    public $viewFolderName = 'user';

    /**
     * @var IRoleRepository
     */
    protected $roleRepository;

    /**
     * @var IPermissionRepository
     */
    protected $permissionRepository;

    /**
     * @var IAdminRepository
     */
    protected $adminRepository;

    /**
     * RoleController constructor.
     * @param IRoleRepository $roleRepository
     * @param IPermissionRepository $permissionRepository
     * @param IAdminRepository $adminRepository
     */
    public function __construct
    (
        IRoleRepository       $roleRepository,
        IPermissionRepository $permissionRepository,
        IAdminRepository      $adminRepository
    )
    {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->adminRepository = $adminRepository;
        $this->baseData['moduleKey'] = 'user';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->baseData['trans_text'] = UserHelper::getLang();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        if ($request->create_date) {
            $createdDates = explode(',', $request->create_date);
            if(isset($createdDates[0])){
                $query->where('created_at', '>=', $createdDates[0]);
            }
            if(isset($createdDates[1])){
                $query->where('created_at', '<=', $createdDates[1]);
            }
        }

        if ($request->role) {
            $role = $request->role;
            $query->whereHas('roles', function ($roleQuery) use ($role) {
                $roleQuery->where('name', '=', $role);
            });
        }

        $this->baseData['allData'] = $query->paginate();

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($id = '')
    {
        try {
            $user = $this->adminRepository->find($id);
            $this->baseData['user'] = !$user ? $user : $user->load('roles');
            $this->baseData['routes']['create_form_data'] = UserHelper::getRoutes()['create_form_data'];
        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $this->baseData['options']['roles'] = $this->roleRepository->all();
            $this->baseData['routes'] = UserHelper::getRoutes();
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
            $user = $this->adminRepository->saveData($request);
        } catch (\Exception $ex) {
            Log::error('Error during roles index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }
        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200, $this->baseData);
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

            $admin = Admin::find($request->get('id'));

            if (count($admin->investors)) {
                throw new \Exception('You are not allowed to delete user, while having investors attached on it!!');
            }

            $admin = $this->adminRepository->find($request->get('id'));
            $admin->delete();

        } catch (\Exception $ex) {
            Log::error('Error during delete user', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }
        return ServiceResponse::jsonNotification($this->baseData['trans_text']['delete_successfully'], 200, $this->baseData);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['email', 'phone']);
        return Excel::download(new AdminsExport($filters), 'users.xlsx');
    }

    public function filterOptions()
    {
        $this->baseData['roles'] = Role::get('name');

        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200, $this->baseData);
    }

}
