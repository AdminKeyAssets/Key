<?php

namespace App\Modules\Admin\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Investor  extends Authenticatable
{
    protected $fillable = [
        'email',
        'password',
        'phone',
        'name',
        'surname',
        'pid',
        'citizenship',
        'address',
        'profile_picture',
        'passport'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
