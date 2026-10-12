<?php

namespace App\Modules\Operational\Master\Models;

use Database\Factories\Master\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;


/**
 * Model Service
 *
 * Katalog jasa medis dan tindakan non-barang yang tersedia di klinik (misal: Konsultasi).
 */
class Service extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasCompanyScope, HasBranchScope, HasUuids, HasSoftDeleteUnique, HasFactory;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'code',
        'name',
        'duration_minutes',
        'price',
        'doctor_fee',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'doctor_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    protected static function newFactory(): Factory
    {
        return ServiceFactory::new();
    }
}
