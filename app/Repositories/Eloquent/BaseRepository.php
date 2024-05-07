<?php

namespace App\Repositories\Eloquent;

use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * @property  user
 */
class BaseRepository implements IBaseRepository
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var
     */
    protected $user;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->model->$method(...$parameters);
    }

    /**
     * Get the associated model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the associated model
     *
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function adminFilter($filter)
    {
        return $this->model->AdminFilters($filter);
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}
