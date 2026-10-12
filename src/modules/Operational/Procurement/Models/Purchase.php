<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;


class Purchase extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;

    protected $table = 'purchases';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_uuid', 'uuid');
    }

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_uuid', 'uuid');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_uuid', 'uuid');
    }
}

