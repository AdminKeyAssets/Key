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
        'notes',
        'currency',
        'status',
        'passport',
        'rent_agreement',
        'rent_end_date',
        'representative',
        'representative_prefix',
        'representative_phone',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
