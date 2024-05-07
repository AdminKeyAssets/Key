<?php


namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;

/**
 * App\Modules\Admin\Models\BaseModel
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admin\Models\BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends Model implements \OwenIt\Auditing\Contracts\Auditable
{

    use Auditable;

}
