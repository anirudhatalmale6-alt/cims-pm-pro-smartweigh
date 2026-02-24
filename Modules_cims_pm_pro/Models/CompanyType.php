<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyType extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyTypeFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => true,
    ];

    public function clients()
    {
        return $this->hasMany(ClientMaster::class, 'company_type_id', 'id');
    }

    public static function getActive()
    {
        return self::where('is_active', 1)
            ->orderBy('type_name', 'asc')
            ->get();
    }

    /**
     * Get company type by code (e.g., "07" returns "Public Company")
     */
    public static function getByCode($code)
    {
        return self::where('type_code', $code)
            ->where('is_active', 1)
            ->first();
    }
}
