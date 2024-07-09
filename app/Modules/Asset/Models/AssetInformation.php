<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInformation extends Model
{
    protected $fillable = ['asset_id', 'key', 'value', 'attachment'];
}
