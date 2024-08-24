<?php

namespace App\Modules\Lead\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
    ];
}
