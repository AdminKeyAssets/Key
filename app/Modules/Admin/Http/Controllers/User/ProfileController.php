<?php

namespace App\Modules\Admin\Http\Controllers\User;

use App\Modules\Admin\Helper\ProfileHelper;
use App\Modules\Admin\Helper\RoleHelper;
use App\Modules\Admin\Helper\UserHelper;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Http\Requests\Profile\SaveProfileRequest;
use App\Modules\Admin\Http\Requests\User\SaveUserRequest;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Profiler\Profile;

class ProfileController extends BaseController
{

    /**
     * @var
     */
    protected $viewFolderName = 'profile.';

    /**
     * @var IAdminRepository
     */
    protected $adminRepository;

    /**
     * RoleController constructor.
     * @param IAdminRepository $adminRepository
     */
    public function __construct
    (
        IAdminRepository $adminRepository
    )
    {
        parent::__construct();
        $this->adminRepository = $adminRepository;
        $this->baseData['moduleKey'] = 'profile';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->baseData['trans_text'] = ProfileHelper::getLang();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {

        try {
            $this->baseData['user'] = \Auth::guard('admin')->user();
            $this->baseData['routes'] = ProfileHelper::getRoutes();
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
            $this->baseData['routes'] = ProfileHelper::getRoutes();
            $this->baseData['user'] = \Auth::guard('admin')->user();
        } catch (\Exception $ex) {
            Log::error('Error during user profile index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification(__('Get profile data successfully'), 200, $this->baseData);
    }

    /**
     * @param SaveProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveProfileRequest $request)
    {
        try {
            $user = \Auth::guard('admin')->user();
            $this->adminRepository->updateProfile($request, $user);

        } catch (\Exception $ex) {
            Log::error('Error during user profile update', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200, $this->baseData);
    }

}
