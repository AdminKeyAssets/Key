<?php

namespace App\Modules\Admin\Models\User;

use App\Modules\Admin\Models\Reminder;
use App\Modules\Asset\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable
{

    use Notifiable, HasRoles,Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'full_name', 'email', 'prefix', 'phone', 'pid', 'profile_picture', 'password'
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
        'rolesId',
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
    public function investors()
    {
        return $this->hasMany(Investor::class);
    }

    public function reminder()
    {
        return $this->hasMany(Reminder::class);
    }

    public function managedAssets()
    {
        return $this->hasMany('App\Modules\Asset\Models\Asset', 'manager_id');
    }

    /**
     * Set the name attribute and update full_name
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->updateFullName();
    }

    /**
     * Set the surname attribute and update full_name
     *
     * @param string $value
     * @return void
     */
    public function setSurnameAttribute($value)
    {
        $this->attributes['surname'] = $value;
        $this->updateFullName();
    }

    /**
     * Set the full_name attribute and update name and surname
     *
     * @param string $value
     * @return void
     */
    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
        
        // Split the full name into name and surname
        $parts = explode(' ', $value, 2);
        $this->attributes['name'] = $parts[0];
        $this->attributes['surname'] = $parts[1] ?? '';
    }

    /**
     * Update full_name based on name and surname
     *
     * @return void
     */
    protected function updateFullName()
    {
        if (isset($this->attributes['name']) || isset($this->attributes['surname'])) {
            $name = $this->attributes['name'] ?? '';
            $surname = $this->attributes['surname'] ?? '';
            $this->attributes['full_name'] = trim("$name $surname");
        }
    }
}
