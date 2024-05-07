<?php


namespace App\Modules\Admin\Http\Controllers\User;

use App\Modules\Admin\Helper\RoleHelper;
use App\Modules\Admin\Helper\UserHelper;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\Role\SaveRoleRequest;
use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @property IRoleRepository roleRepository
 * @property IPermissionRepository permissionRepository
 * @property IAdminRepository adminRepository
 */
class RoleController extends BaseController
{

    /**
     * @var
     */
    protected $viewFolderName = 'role.';

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
        IRoleRepository $roleRepository,
        IPermissionRepository $permissionRepository,
        IAdminRepository $adminRepository
    )
    {
        parent::__construct();
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->adminRepository = $adminRepository;
        $this->baseData['moduleKey'] = 'role';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->baseData['trans_text'] = RoleHelper::getLang();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {

        $this->baseData['allData'] = $this->roleRepository->paginate();

        return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($id = '')
    {

        try {

            // Find role.
            $role = $this->roleRepository->find($id);

            // Set role.
            $this->baseData['role'] = !$role ? $role : $role->load('permissions');

            // Set routes.
            $this->baseData['routes'] = RoleHelper::getRoutes();

        } catch (\Exception $ex) {
            return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }

        return  view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {

        try {

            $this->baseData['options']['permissions'] = $this->permissionRepository->all();
            $this->baseData['routes'] = RoleHelper::getRoutes();

        } catch (\Exception $ex) {
            Log::error('Error during roles index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200,  $this->baseData );
    }

    /**
     * @param SaveRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveRoleRequest $request)
    {
        try {

            // Save role.
            $this->roleRepository->saveData($request);

        } catch (\Exception $ex) {
            Log::error('Error during roles index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200,  $this->baseData );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {

        try {

            $role = $this->roleRepository->find($request->get('id'));

            if ($role->name == 'administrator') {
                throw new \Exception('ადმინისტრატორის როლს ვერ წაშლით!');
            }

            $role->delete();

        } catch (\Exception $ex) {
            Log::error('Error during delete role', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['delete_successfully'], 200,  $this->baseData );

    }

}
