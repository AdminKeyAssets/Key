<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentValue extends Model
{
    protected $fillable = ['asset_id', 'value', 'date', 'currency', 'attachment'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
