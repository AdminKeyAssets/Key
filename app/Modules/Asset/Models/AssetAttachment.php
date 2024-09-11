<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAttachment extends Model
{
    protected $fillable = [
        'path',
        'image',
        'asset_id',
        'name',
        'type',
        'is_main'
    ];
}
