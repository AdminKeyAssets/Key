<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperAgreement extends Model
{
    protected $fillable = ['developer_id', 'name', 'path'];
}
