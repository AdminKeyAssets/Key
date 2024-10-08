<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
class PaymentsHistory extends Model
{

    protected $fillable = ['asset_id', 'date', 'amount', 'attachment', 'currency'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
