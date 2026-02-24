<?php
namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMasterLookup extends Model
{
    protected $table = 'client_master_lookups';

    protected $fillable = [
        'category', 'code', 'value', 'sort_order', 'is_active'
    ];

    /**
     * Get lookup values by category
     */
    public static function getByCategory($category)
    {
        return self::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get();
    }
}
