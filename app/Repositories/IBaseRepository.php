<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

interface IBaseRepository
{

    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @param $model
     * @return mixed
     */
    public function setModel($model);

    /**
     * @param $filter
     * @return mixed
     */
    public function adminFilter($filter);

    /**
     * @param $user
     * @return mixed
     */
    public function setUser($user);

}
