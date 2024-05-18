<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['month', 'payment_date', 'status', 'amount', 'asset_id', 'attachment'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
