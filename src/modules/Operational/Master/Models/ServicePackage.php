<?php

namespace App\Modules\Operational\Master\Models;

use Database\Factories\Master\ServicePackageFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Shared\Traits\HasSoftDeleteUnique;


/**
 * Model ServicePackage
 *
 * Menyimpan data paket layanan medis yang menggabungkan beberapa jasa.
 */
class ServicePackage extends Model implements AuditableContract {
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
        'price',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<ServicePackageItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ServicePackageItem::class, 'service_package_uuid', 'uuid');
    }
    protected static function newFactory(): Factory
    {
        return ServicePackageFactory::new();
    }
}
