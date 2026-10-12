<?php

namespace App\Modules\Operational\Inventory\Models;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Core\Organization\Models\Branch;

class StockAdjustment extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasCompanyScope, HasBranchScope, HasUuids, HasSoftDeleteUnique;

    protected $table = 'stock_adjustments';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'stock_adjustment_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }
}
