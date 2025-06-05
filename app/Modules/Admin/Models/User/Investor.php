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
        'service_agreement',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the investor's full name.
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
     * Set the investor's full name.
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
}
