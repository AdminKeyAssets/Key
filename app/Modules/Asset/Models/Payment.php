<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['month_count', 'payment_date', 'status', 'asset_id'];
}
