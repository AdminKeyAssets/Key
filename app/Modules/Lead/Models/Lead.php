<?php

namespace App\Modules\Lead\Models;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'full_name',
        'email',
        'phone',
        'prefix',
        'admin_id',
        'status',
        'marketing_channel',
    ];
    
    /**
     * Set the name attribute and update full_name
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->updateFullName();
    }

    /**
     * Set the surname attribute and update full_name
     *
     * @param string $value
     * @return void
     */
    public function setSurnameAttribute($value)
    {
        $this->attributes['surname'] = $value;
        $this->updateFullName();
    }

    /**
     * Set the full_name attribute and update name and surname
     *
     * @param string $value
     * @return void
     */
    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
        
        // Split the full name into name and surname
        $parts = explode(' ', $value, 2);
        $this->attributes['name'] = $parts[0];
        $this->attributes['surname'] = $parts[1] ?? '';
    }

    /**
     * Update full_name based on name and surname
     *
     * @return void
     */
    protected function updateFullName()
    {
        if (isset($this->attributes['name']) || isset($this->attributes['surname'])) {
            $name = $this->attributes['name'] ?? '';
            $surname = $this->attributes['surname'] ?? '';
            $this->attributes['full_name'] = trim("$name $surname");
        }
    }
}
