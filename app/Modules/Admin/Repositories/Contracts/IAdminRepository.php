<?php

namespace App\Modules\Admin\Repositories\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Http\Request;

interface IAdminRepository extends IBaseRepository
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function saveData(Request $request);

    /**
     * @param $request
     * @param $user
     * @return mixed
     */
    public function updateProfile($request, $user);

    /**
     * @param $params
     * @return mixed
     */
    public function filterData($params);

    /**
     * @param $data
     * @param $sort
     * @return mixed
     */
    public function sortData($data,$sort);

    /**
     * @param $user
     * @return mixed
     */
    public function setUser($user);

    /**
     * @return mixed
     */
    public function getAllPermissions();

}
