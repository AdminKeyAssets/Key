<?php

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;

class News extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use SoftDeletes, Auditable;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'thumbnail',
        'admin_id',
        'developer_id',
        'manager_id',
        'created_by_type',
        'status',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'created_by_name',
        'manager_name'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function developer()
    {
        return $this->belongsTo(\App\Modules\Admin\Models\User\Developer::class);
    }

    public function manager()
    {
        return $this->belongsTo(Admin::class, 'manager_id');
    }

    public function investors()
    {
        return $this->belongsToMany(Investor::class, 'news_investors');
    }

    public function images()
    {
        return $this->hasMany(NewsImage::class);
    }

    public function getCreatedByNameAttribute()
    {
        if ($this->created_by_type === 'developer' && $this->developer) {
            return $this->developer->full_name ?? $this->developer->name . ' ' . $this->developer->surname;
        } elseif ($this->admin) {
            return $this->admin->full_name ?? $this->admin->name . ' ' . $this->admin->surname;
        }
        return '';
    }

    public function getManagerNameAttribute()
    {
        if ($this->manager) {
            return $this->manager->full_name ?? $this->manager->name . ' ' . $this->manager->surname;
        }
        return '';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForDeveloper($query, $developerId)
    {
        return $query->where('developer_id', $developerId);
    }

    public function scopeForInvestor($query, $investorId)
    {
        return $query->whereHas('investors', function ($q) use ($investorId) {
            $q->where('investor_id', $investorId);
        });
    }

    /**
     * Relationship to read tracking
     */
    public function readByInvestors()
    {
        return $this->hasMany(\App\Modules\Admin\Models\InvestorNewsRead::class, 'news_id');
    }

    /**
     * Check if news is read by specific investor
     */
    public function isReadByInvestor($investorId)
    {
        return $this->readByInvestors()->where('investor_id', $investorId)->exists();
    }

    /**
     * Mark news as read by investor
     */
    public function markAsReadByInvestor($investorId)
    {
        return $this->readByInvestors()->firstOrCreate([
            'investor_id' => $investorId,
            'news_id' => $this->id
        ], [
            'read_at' => now()
        ]);
    }

    /**
     * Get count of unread news for specific investor
     */
    public static function getUnreadCountForInvestor($investorId)
    {
        return static::published()
            ->forInvestor($investorId)
            ->whereDoesntHave('readByInvestors', function ($query) use ($investorId) {
                $query->where('investor_id', $investorId);
            })
            ->count();
    }
}
