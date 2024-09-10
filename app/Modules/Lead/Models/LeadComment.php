<?php

namespace App\Modules\Lead\Models;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Eloquent\Model;

class LeadComment extends Model
{
    protected $fillable = [
        'comment',
        'attachment',
        'admin_id',
        'lead_id'
    ];


    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
