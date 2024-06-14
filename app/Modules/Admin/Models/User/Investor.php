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
        'admin_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
