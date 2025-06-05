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
     * Get the admin's full name.
     * 
     * @return string
     */
    public function getFullNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        
        return trim($this->name . ' ' . $this->surname);
    }

    /**
     * Set the admin's full name.
     *
     * @param string $value
     * @return void
     */
    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
        
        // For backward compatibility, also set name and surname
        $nameParts = explode(' ', $value, 2);
        $this->attributes['name'] = $nameParts[0] ?? '';
        $this->attributes['surname'] = $nameParts[1] ?? '';
    }

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

}
