<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorNewsRead extends Model
{
    protected $table = 'investor_news_read';
    
    protected $fillable = [
        'investor_id',
        'news_id',
        'read_at'
    ];

    protected $dates = [
        'read_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Relationship to News
     */
    public function news()
    {
        return $this->belongsTo(\App\Modules\Admin\Models\News::class, 'news_id');
    }

    /**
     * Relationship to Investor
     */
    public function investor()
    {
        return $this->belongsTo(\App\Modules\Admin\Models\User\Investor::class, 'investor_id');
    }
}
