<?php

namespace Modules\CIMSDocManager\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $table = 'cims_document_categories';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function types()
    {
        return $this->hasMany(DocumentType::class, 'category_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
