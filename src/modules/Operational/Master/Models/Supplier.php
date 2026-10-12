<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Operational\Procurement\Models\Purchase;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Database\Factories\Master\SupplierFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Model Supplier
 *
 * Master data pemasok yang menyediakan barang dagangan, jasa, atau aset bagi klinik.
 */
class Supplier extends Model implements AuditableContract
{
    protected $dateFormat = 'U';

    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasSoftDeleteUnique, HasUuids;

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'name',
        'phone',
        'email',
        'address',
        'term_days',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'term_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_uuid', 'uuid');
    }

    protected static function newFactory(): Factory
    {
        return SupplierFactory::new();
    }
}
