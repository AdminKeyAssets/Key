<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class AssetGallery extends Model
{
    protected $fillable = ['asset_id', 'name', 'image'];
}
