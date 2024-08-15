<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = ['asset_id', 'date', 'status', 'amount', 'attachment', 'currency'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
