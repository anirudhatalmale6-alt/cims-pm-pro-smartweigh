<?php

namespace Modules\CIMSDocManager\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPeriod extends Model
{
    protected $table = 'cims_document_periods';

    protected $fillable = [
        'period_name',
        'tax_year',
        'display_order',
        'is_active',
        'period_combo',
        'period_description',
        'show_in_return_input',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_return_input' => 'boolean',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'period_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'desc');
    }
}
