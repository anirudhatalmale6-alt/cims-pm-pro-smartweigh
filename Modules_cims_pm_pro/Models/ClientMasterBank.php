<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientMasterBank extends Model
{
    use SoftDeletes;

    protected $table = 'client_master_banks';

    protected $fillable = [
        'client_id',
        'bank_id',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'bank_account_type_id',
        'bank_account_type_name',
        'account_status',
        'bank_branch_name',
        'bank_branch_code',
        'bank_swift_code',
        'bank_account_date_opened',
        'confirmation_of_banking_uploaded',
        'is_active',
        'is_checked',
        'bank_account_status_id',
        'bank_account_status_name',
        'bank_statement_frequency_id',
        'bank_statement_frequency_name',
        'bank_statement_cut_off_date',
        'is_default',
        'document_id',
    ];

    protected $casts = [
        'bank_account_date_opened' => 'date',
        'confirmation_of_banking_uploaded' => 'boolean',
        'is_active' => 'boolean',
        'is_checked' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(ClientMaster::class, 'client_id', 'client_id');
    }

    public function bank()
    {
        return $this->belongsTo(RefBank::class, 'bank_id', 'id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

}
