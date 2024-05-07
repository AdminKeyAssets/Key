<?php

namespace App\Modules\Admin\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Modules\Admin\Models\User\Admin
 *
 * @property mixed roles
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read array $roles_id
 * @property-read string $roles_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\User\Admin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Admin extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable
{

    use Notifiable, HasRoles,Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'rolesName',
        'rolesId'
    ];

    /**
     * @return array
     */
    public function getRolesIdAttribute()
    {
        return $this->roles ? $this->roles->pluck('id')->toArray() : [];
    }

    /**
     * @return string
     */
    public function getRolesNameAttribute()
    {
        return $this->roles ? implode(',',$this->roles->pluck('name')->toArray()) : '';
    }

}
