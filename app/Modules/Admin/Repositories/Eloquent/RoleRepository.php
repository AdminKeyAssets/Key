<?php


namespace App\Modules\Admin\Repositories\Eloquent;

use App\Models\Role;
use App\Modules\Admin\Repositories\Contracts\IRoleRepository;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleRepository extends BaseRepository implements IRoleRepository
{

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var Request
     */
    protected $request;

    /**
     * RoleRepository constructor.
     * @param Role $model
     */
    public function __construct
    (
        Role $model
    )
    {
        parent::__construct($model);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function filterData($params)
    {
        return Role::Filter($params);
    }

    /**
     * @param $data
     * @param $sort
     * @return Builder
     */
    public function sortData($data,$sort)
    {
        return Role::sort($data, $sort);
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

            $updateInfo = $request->only(['guard_name', 'name']);

            if ( !empty($request->get('id')) ) {

                $this->role = $this->find($request->get('id'));

                if ($this->role->name != 'administrator') {
                    $this->role->update($updateInfo);
                }

            } else {
                $this->role = $this->create($updateInfo);
            }

            if ( !empty($request->get('permissions') ) ) {
                $this->role->permissions()->sync($request->get('permissions'));
            } else {
                $this->role->permissions()->detach();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }

    }

}
