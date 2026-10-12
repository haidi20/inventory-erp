<?php

namespace App\Modules\Operational\Inventory\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Core\Organization\Models\Branch;


class StockTransfer extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;

    protected $table = 'stock_transfers';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(StockTransferItem::class, 'stock_transfer_uuid', 'uuid');
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Location::class, 'source_location_uuid', 'uuid');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Location::class, 'destination_location_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }
}

