<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMasterAddress extends Model
{
    protected $table = 'client_master_addresses';

    protected $fillable = [
        'client_id', 'address_id', 'address_type', 'is_checked', 'is_default'
    ];

    public function client()
    {
        return $this->belongsTo(ClientMaster::class, 'client_id', 'client_id');
    }

    /**
     * Get the address from the addresses module
     * Note: This assumes the mod_addresses_addresses table exists
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function address_type()
    {
        return $this->belongsTo(CimsAddressType::class, 'address_type_id', 'id');
    }
}
