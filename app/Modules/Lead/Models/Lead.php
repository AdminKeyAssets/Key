<?php

namespace App\Modules\Lead\Models;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'prefix',
        'admin_id',
        'status',
        'marketing_channel',
    ];
}
