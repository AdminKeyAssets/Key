<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'asset_id',
        'name',
        'surname',
        'id_number',
        'citizenship',
        'email',
        'phone',
        'prefix',
        'agreement_date',
        'agreement_term',
        'monthly_rent',
        'currency',
        'status',
        'passport'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
