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
        'left_amount',
        'asset_id',
        'currency'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
