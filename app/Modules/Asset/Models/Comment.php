<?php

namespace App\Modules\Asset\Models;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'comment', 'read', 'attachment', 'admin_id', 'asset_id', 'investor_id'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }
}
