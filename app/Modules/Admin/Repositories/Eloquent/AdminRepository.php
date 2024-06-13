<?php


namespace App\Modules\Admin\Repositories\Eloquent;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Repositories\Contracts\IAdminRepository;
use App\Repositories\Eloquent\BaseRepository;
use App\Services\MailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;

class AdminRepository extends BaseRepository implements IAdminRepository
{

    /**
     * @var Admin
     */
    protected $admin;

    /**
     * @var Request
     */
    protected $request;
    public $mailService;

    /**
     * AdminRepository constructor.
     * @param Admin $model
     */
    public function __construct
    (
        Admin       $model,
        MailService $mailService
    )
    {
        parent::__construct($model);
        $this->mailService = $mailService;
    }

    /**
     * @param $request
     * @param $user
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateProfile($request, $user)
    {
        try {
            DB::beginTransaction();

            /**
             * Set request.
             */
            $this->request = $request;

            $data = $request->only(['name', 'email', 'phone']);

            if ($this->request->get('password')) {
                $data['password'] = bcrypt($this->request->get('password'));
            }

            $this->admin = $user;
            $this->admin->update($data);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage(), 500);
        }

    }

    /**
     * @param $request Request
     * @throws \Exception
     */
    public function saveData(Request $request)
    {
        try {
            DB::beginTransaction();

            /**
             * Set request.
             */
            $this->request = $request;

            $data = $request->only(['name', 'email', 'phone']);

            if ($this->request->get('password')) {
                $data['password'] = bcrypt($this->request->get('password'));
            }

            if (!empty($request->get('id'))) {

                $this->admin = $this->find($request->get('id'));
                $this->admin->update($data);

            } else {
                $this->admin = $this->create($data);

                $this->mailService->sendEmail(
                    $data['email'],
                    'KeyAsset Credentials',
                    'Your email is: ' . $data['email'] .
                    'Your password is: ' . $this->request->get('password')
                );
            }

            if (!empty($request->get('roles'))) {
                $this->admin->roles()->sync($request->get('roles'));
            } else {
                $this->admin->roles()->detach();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage(), 500);
        }

    }

    /**
     * @param $user
     * @return $this|mixed
     */
    public function setUser($user)
    {
        $this->admin = $user;
        return $this;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function filterData($params)
    {
        return Admin::filter($params);
    }

    /**
     * @param $data
     * @param $sort
     * @return Builder
     */
    public function sortData($data, $sort)
    {
        return Admin::sort($data, $sort);
    }

    /**
     * @return mixed
     */
    public function getAllPermissions()
    {
        $permissions = $this->admin->permissions;
        $roles = $this->admin->roles;

        foreach ($roles as $role) {

            if (!$role->permissions->count()) {
                continue;
            }

            foreach ($role->permissions as $perms) {
                $permissions->push($perms);
            }
        }

        return $permissions;
    }

}
