<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMasterAudit extends Model
{
    protected $table = 'client_master_audit';
    protected $primaryKey = 'audit_id';

    protected $fillable = [
        'client_id', 'user_id', 'action', 'old_values', 'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(ClientMaster::class, 'client_id', 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
}
