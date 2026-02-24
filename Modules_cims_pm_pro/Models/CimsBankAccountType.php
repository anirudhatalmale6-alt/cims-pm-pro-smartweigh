<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CimsBankAccountType extends Model
{
    protected $table = 'cims_bank_account_types';
    public $timestamps = false;

    public function accountTypes(): BelongsToMany
    {
        return $this->belongsToMany(RefBankAccountType::class, 'ref_bank_account_type_pivot', 'ref_bank_id', 'ref_bank_account_type_id')
            ->withPivot('is_active');
    }
}
