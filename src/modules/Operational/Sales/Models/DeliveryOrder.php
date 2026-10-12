<?php

namespace App\Modules\Operational\Sales\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Person;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;

use App\Modules\Operational\Master\Models\Location;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Database\Factories\Operational\DeliveryOrderFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model DeliveryOrder
 *
 * Dokumen pengiriman barang atau obat ke pelanggan atau antar gudang internal.
 */
class DeliveryOrder extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $table = 'delivery_orders';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'delivery_order_uuid', 'uuid');
    }

    public function details()
    {
        return $this->items();
    }
    protected static function newFactory(): Factory
    {
        return DeliveryOrderFactory::new();
    }
}
