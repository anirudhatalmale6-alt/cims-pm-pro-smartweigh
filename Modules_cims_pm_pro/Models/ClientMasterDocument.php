<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMasterDocument extends Model
{
    protected $table = 'client_master_documents';
    protected $primaryKey = 'id';

    protected $fillable = [
        'client_id',
        'client_code',
        'document_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'uploaded_by'
    ];

    protected $dates = [
        'uploaded_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the client that owns this document
     */
    public function client()
    {
        return $this->belongsTo(ClientMaster::class, 'client_id', 'client_id');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by', 'id');
    }

    /**
     * Get all documents for a client by type
     */
    public static function getByClientAndType($clientId, $documentType)
    {
        return self::where('client_id', $clientId)
            ->where('document_type', $documentType)
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    /**
     * Get the latest document for a client by type
     */
    public static function getLatestByClientAndType($clientId, $documentType)
    {
        return self::where('client_id', $clientId)
            ->where('document_type', $documentType)
            ->orderBy('uploaded_at', 'desc')
            ->first();
    }

    /**
     * Generate the stored filename
     * Format: ClientCode DocType - Uploaded Day DD Mon YYYY @ HH:MM:SS.ext
     */
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
