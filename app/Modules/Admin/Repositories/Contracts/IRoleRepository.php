<?php


namespace App\Modules\Admin\Repositories\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Http\Request;

interface IRoleRepository extends IBaseRepository
{

    /**
     * @param $request
     * @return mixed
     */
    public function saveData(Request $request);

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

}
