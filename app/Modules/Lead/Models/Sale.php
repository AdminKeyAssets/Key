<?php

namespace App\Modules\Lead\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'project',
        'investor',
        'type',
        'size',
        'price',
        'currency',
        'total_price',
        'agreement_status',
        'agreement_date',
        'down_payment',
        'period',
        'marketing_channel',
    ];
}
