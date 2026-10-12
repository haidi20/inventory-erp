<?php

namespace App\Modules\Operational\Master\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Database\Factories\Master\EmployeeFactory;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Modules\Core\Identity\Models\User;


/**
 * Model Employee
 *
 * Data induk karyawan, tenaga medis, dan staf administratif beserta informasi personalnya.
 */
class Employee extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'user_uuid',
        'nik',
        'name',
        'sip_number',
        'str_number',
        'ihs_practitioner_id',
        'specialization',
        'role',
        'fee_default',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fee_default' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return EmployeeFactory::new();
    }
}
