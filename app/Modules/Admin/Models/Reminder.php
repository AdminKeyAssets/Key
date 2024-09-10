<?php

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['admin_id', 'comment', 'reminder_date', 'done'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
