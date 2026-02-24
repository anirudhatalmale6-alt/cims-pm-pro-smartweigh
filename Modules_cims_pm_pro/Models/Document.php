<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $table = 'cims_documents';

    protected $fillable = [
        'title',
        'document_code',
        'document_ref',
        'file_original_name',
        'file_stored_name',
        'file_mime_type',
        'file_path',
        'client_id',
        'client_name',
        'client_code',
        'client_email',
        'registration_number',
        'category_id',
        'type_id',
        'doc_group',
        'period_id',
        'period_name',
        'period_combo',
        'financial_year',
        'issue_date',
        'expiry_date',
        'date_registered',
        'has_expiry',
        'lead_time_days',
        'days_to_expire',
        'status',
        'show_as_current',
        'is_archived',
        'is_trashed',
        'description',
        'notes',
        'uploaded_by',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'issue_date',
        'expiry_date',
        'date_registered',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'show_as_current' => 'boolean',
        'is_archived' => 'boolean',
        'is_trashed' => 'boolean',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'date_registered' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'type_id');
    }

    public function period()
    {
        return $this->belongsTo(DocumentPeriod::class, 'period_id');
    }

    public function getFileIconAttribute()
    {
        $icons = config('cimsdocmanager.document_icons', []);
        $ext = strtolower($this->file_mime_type);
        return $icons[$ext] ?? 'fa-file text-secondary';
    }

    public function getFileSizeFormattedAttribute()
    {
        $path = public_path(config('cimsdocmanager.upload_path') . $this->file_stored_name);
        if (file_exists($path)) {
            $bytes = filesize($path);
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            }
            return $bytes . ' bytes';
        }
        return 'N/A';
    }

    public function getIsExpiredAttribute()
    {
        if ($this->has_expiry === 'YES' && $this->expiry_date) {
            return $this->expiry_date->isPast();
        }
        return false;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if ($this->has_expiry === 'YES' && $this->expiry_date) {
            return now()->diffInDays($this->expiry_date, false);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false)->where('is_trashed', false);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('has_expiry', 'YES')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public static function generateStoredFilename($clientCode, $documentType, $extension)
    {
        $now = now();
        // Windows filesystems do not allow ':' in filenames, so use a safe time format.
        $dateStr = $now->format('D d M Y') . ' @ ' . $now->format('H-i-s');

        // Clean the document type for filename
        $cleanDocType = preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $documentType);

        return $clientCode . ' ' . $cleanDocType . ' - Uploaded ' . $dateStr . '.' . $extension;
    }
}
