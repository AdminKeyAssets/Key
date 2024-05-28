<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    protected $fillable = [
        'price',
        'date_from',
        'date_to',
        'asset_id'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
