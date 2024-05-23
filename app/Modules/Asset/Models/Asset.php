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
        'total_price'
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
