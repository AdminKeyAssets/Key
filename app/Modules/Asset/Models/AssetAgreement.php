<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAgreement extends Model
{
    protected $fillable = ['asset_id', 'name', 'attachment'];
}
