<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CimsVatCycle extends Model
{
    /** @use HasFactory<\Database\Factories\VatCycleFactory> */
    use HasFactory;

    protected $table = 'cims_vat_cycles';
}
