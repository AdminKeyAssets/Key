<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'number',
        'payment_date',
        'status',
        'amount',
        'asset_id',
        'attachment',
        'currency'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
