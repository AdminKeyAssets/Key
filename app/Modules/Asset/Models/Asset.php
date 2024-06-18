<?php

namespace App\Modules\Asset\Models;

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
        'agreement_date',
        'first_payment_date',
        'period',
        'total_agreement_price',
        'floor_plan',
        'flat_plan',
        'agreement',
        'ownership_certificate'
    ];

    public function informations()
    {
        return $this->hasMany(AssetInformation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function attachments()
    {
        return $this->hasMany(AssetAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
