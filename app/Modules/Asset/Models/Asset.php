<?php

namespace App\Modules\Asset\Models;

use App\Modules\Admin\Models\User\Investor;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'icon',
        'name',
        'address',
        'cadastral_number',
        'document',
        'investor_id',
        'city',
        'delivery_date',
        'area',
        'total_price',
        'admin_id',
        'currency',
        'project_name',
        'project_description',
        'project_link',
        'location',
        'type',
        'floor',
        'flat_number',
        'price',
        'condition',
        'agreement_status',
        'asset_status',
        'agreement_date',
        'first_payment_date',
        'period',
        'floor_plan',
        'flat_plan',
        'agreement',
        'ownership_certificate',
        'current_value',
        'current_value_currency',
        'delivery_condition_description',
        'total_floors',
        'renovation_agreement_date',
        'renovation_first_payment_date',
        'renovation_agreement',
        'renovation_total_price',
        'renovation_period'
    ];

    public function informations()
    {
        return $this->hasMany(AssetInformation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
    public function renovationPayments()
    {
        return $this->hasMany(RenovationPayment::class);
    }
    public function attachments()
    {
        return $this->hasMany(AssetAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function currentValues()
    {
        return $this->hasMany(CurrentValue::class)->orderByDesc('id');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function paymentsHistories()
    {
        return $this->hasMany(PaymentsHistory::class);
    }

    public function Investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function rentalPaymentsHistories()
    {
        return $this->hasMany(RentalPaymentsHistory::class);
    }
    public function renovationPaymentsHistories()
    {
        return $this->hasMany(RenovationPaymentsHistory::class);
    }

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function investors()
    {
        return $this->belongsToMany(Investor::class);
    }

    public function agreements()
    {
        return $this->hasMany(AssetAgreement::class);
    }

    public function gallery()
    {
        return $this->hasMany(AssetGallery::class);
    }
}
