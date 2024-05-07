<?php

namespace App\Modules\Admin\Models\Statics;

use App\Modules\Admin\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Modules\Admin\Models\Statics\File
 *
 * @property mixed src
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $fileable
 * @property-read string $full_url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Admin\Models\Statics\File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File query()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Admin\Models\Statics\File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Admin\Models\Statics\File withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $src
 * @property string|null $file_type
 * @property string|null $type
 * @property int|null $fileable_id
 * @property string|null $fileable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereFileableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereFileableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereSrc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\Statics\File whereUpdatedAt($value)
 */
class File extends BaseModel
{

    use SoftDeletes;

    const ORIGINAL_FILE_FOLDER_NAME = 'original';

    /**
     * @var string
     */
    protected $table = 'files';

    /**
     * @var array
     */
    protected $fillable = [
        'src',
        'type',
        'file_type',
        'imageable_id',
        'imageable_type'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    protected $appends = [
        'fullUrl'
    ];

    /**
     * Get the owning fileable model.
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * @return string
     */
    public function getFullUrlAttribute()
    {
        return asset('storage/' . $this->src);
    }

}
