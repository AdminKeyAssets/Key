<?php

namespace App\Modules\News\Models;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;

class News extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use Auditable;

    protected $fillable = [
        'title',
        'content',
        'image',
        'status',
        'admin_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure admin_id is set before creating
        static::creating(function ($news) {
            if (!$news->admin_id) {
                $news->admin_id = auth()->user()->id;
            }
        });

        // Clean up image on deletion
        static::deleting(function ($news) {
            if ($news->image && \Storage::disk('public')->exists($news->image)) {
                \Storage::disk('public')->delete($news->image);
            }
        });
    }

    /**
     * Get the admin/manager who created this news
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the investors attached to this news
     */
    public function investors()
    {
        return $this->belongsToMany(Investor::class, 'news_investor');
    }

    /**
     * Scope to get published news
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get draft news
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get formatted investors names
     */
    public function getInvestorsNamesAttribute()
    {
        return $this->investors ? $this->investors->map(function($investor) {
            return $investor->name . ' ' . $investor->surname;
        })->implode(', ') : '';
    }

    /**
     * Get investors IDs array
     */
    public function getInvestorsIdsAttribute()
    {
        return $this->investors ? $this->investors->pluck('id')->toArray() : [];
    }
}
