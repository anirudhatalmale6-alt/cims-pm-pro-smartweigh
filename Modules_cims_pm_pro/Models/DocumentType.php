<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $table = 'cims_document_types';

    protected $fillable = [
        'name',
        'is_active',
        'category_id',
        'doc_ref',
        'doc_group',
        'description',
        'lead_time_days',
        'has_expiry',
        'days_to_expire',
        'client_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_expiry' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
