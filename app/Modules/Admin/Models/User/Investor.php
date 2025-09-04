<?php

namespace App\Modules\Admin\Models\User;

use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Investor  extends Authenticatable
{
    protected $fillable = [
        'email',
        'password',
        'prefix',
        'phone',
        'name',
        'surname',
        'full_name',
        'pid',
        'citizenship',
        'address',
        'profile_picture',
        'passport',
        'admin_id',
        'is_demo',
        'is_archived',
        'service_agreement',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
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
