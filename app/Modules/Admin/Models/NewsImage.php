<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class NewsImage extends Model
{
    protected $fillable = [
        'news_id',
        'image',
        'name',
        'order',
        'is_thumbnail'
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
