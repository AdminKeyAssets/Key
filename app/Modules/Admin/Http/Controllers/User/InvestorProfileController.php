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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Profiler\Profile;

class InvestorProfileController extends BaseController
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
            $user = \Auth::guard('investor')->user();
            $this->baseData['user'] = $user;
            $this->baseData['routes'] = ProfileHelper::getRoutes('investor');
            $manager = Admin::where('id', $user->admin_id)->first();
            $this->baseData['user']['manager'] = $manager->name . ' ' . $manager->surname;
        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_edit', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_edit', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $user = \Auth::guard('investor')->user();
            $this->baseData['user'] = $user;
            $this->baseData['routes'] = ProfileHelper::getRoutes('investor');
            $manager = Admin::where('id', $user->admin_id)->first();
            $this->baseData['manager'] = $manager->name . ' ' . $manager->surname;
            $this->baseData['countries'] = Country::get('country');
            $this->baseData['prefixes'] = Country::groupBy('prefix')->get('prefix');

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
            $user = \Auth::guard('investor')->user();
            $profilePicture = null;

            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $profilePictureFile = $request->file('profile_picture');
                $profilePicture = $profilePictureFile->store('uploads', 'public');
                $profilePicture = Storage::url($profilePicture);

            } else if ($request->input('profile_picture') === null) {
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
            } else {
                $profilePicture = $request->input('profile_picture');
            }

            $investorData = [
                'email' => $request->email,
                'prefix' => $request->prefix,
                'phone' => $request->phone,
                'pid' => $request->pid,
                'citizenship' => $request->citizenship,
                'address' => $request->address,
                'profile_picture' => $profilePicture,
            ];
            if($request->password){
                $investorData['password'] = bcrypt($request->password);
            }

            Investor::where('id', $user->getAuthIdentifier())->update($investorData);

        } catch (\Exception $ex) {
            Log::error('Error during user profile update', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200, $this->baseData);
    }

}
