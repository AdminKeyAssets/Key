<?php

namespace App\Modules\Admin\Models\User;

use App\Modules\Asset\Models\Asset;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

class Developer extends Authenticatable
{
    protected $fillable = [
        'name',              // Developer Name
        'id_code',           // ID Code
        'representative',    // Representative name
        'tel',               // Telephone
        'representative_position', // Representative Position
        'service_agreement', // Service Agreement file path
        'logo',              // Logo file path
        'stamp',             // Stamp file path
        'signature',         // Signature file path
        'username',          // Username for login
        'password',          // Password
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * Get assets associated with the developer by name matching
     */
    public function assets()
    {
        return Asset::where('project_name', $this->name);
    }
}
