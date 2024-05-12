<?php

namespace App\Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['icon', 'name', 'address', 'cadastral_number', 'document', 'investor_id'];

    public function informations() {
        return $this->hasMany(AssetInformation::class);
    }
}
