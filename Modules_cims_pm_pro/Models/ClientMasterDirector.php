<?php

namespace Modules\cims_pm_pro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMasterDirector extends Model
{
    /** @use HasFactory<\Database\Factories\CimsDirectorFactory> */
    use HasFactory;

    protected $table = 'client_master_directors';
}
