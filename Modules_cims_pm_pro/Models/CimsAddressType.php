<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CimsAddressType extends Model
{
    protected $table = 'cims_address_types';
    public $timestamps = false;


    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
