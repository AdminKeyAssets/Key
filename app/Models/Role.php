<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Auditable;

/**
 * App\Models\Role
 *
 * @property mixed permissions
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read array $permissions_id
 * @property-read string $permissions_name
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Admin\Models\User\Admin[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Role permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends \Spatie\Permission\Models\Role implements \OwenIt\Auditing\Contracts\Auditable
{

    use Auditable;

    /**
     * @var array
     */
    public $appends = [
        'permissionsId',
        'permissionsName'
    ];

    /**
     * @return string
     */
    public function getPermissionsNameAttribute()
    {
        return $this->permissions ? implode(',',$this->permissions->pluck('label')->toArray()) : '';
    }

    /**
     * @return array
     */
    public function getPermissionsIdAttribute()
    {
        return $this->permissions ? $this->permissions->pluck('id')->toArray() : [];
    }

}
