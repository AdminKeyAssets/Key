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
        'pid',
        'citizenship',
        'address',
        'profile_picture',
        'passport',
        'admin_id',
        'is_demo'
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
        return $this->hasMany(Asset::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
