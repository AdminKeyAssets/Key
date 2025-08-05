<?php

namespace App\Modules\Asset\Models;

use App\Modules\Admin\Models\User\Developer;
use Illuminate\Database\Eloquent\Model;

class DeveloperAsset extends Model
{
    protected $fillable = [
        'developer_id',
        'asset_name',
    ];

    public function developer()
    {
        return $this->belongsTo(Developer::class);
    }
}
