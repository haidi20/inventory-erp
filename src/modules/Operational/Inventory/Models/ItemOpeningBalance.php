<?php

namespace App\Modules\Operational\Inventory\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\MasterProduct;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model ItemOpeningBalance
 *
 * Saldo awal stok barang pada saat migrasi data atau awal implementasi sistem.
 */
class ItemOpeningBalance extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $table = 'item_opening_balances';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    /** @return BelongsTo<MasterProduct, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'item_uuid', 'uuid');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    /** @return BelongsTo<Location, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }
}




