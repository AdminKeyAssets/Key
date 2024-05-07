<?php


namespace App\Modules\Admin\Repositories\Eloquent;

use App\Models\Permission;
use App\Modules\Admin\Repositories\Contracts\IPermissionRepository;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends BaseRepository implements IPermissionRepository
{

    /**
     * PermissionRepository constructor.
     * @param Permission $model
     */
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

}
