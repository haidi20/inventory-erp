<?php

namespace App\Modules\Core\Organization\Models;

use App\Modules\Operational\Master\Models\MasterProduct;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Inventory\Models\StockAdjustment;
use App\Modules\Operational\Inventory\Models\StockTransfer;
use App\Modules\Finance\Ledger\Models\Transaction;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Database\Factories\Master\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Database\Eloquent\Model;


/**
 * Model Company
 *
 * Informasi legal dan profil utama entitas perusahaan pemilik klinik/rumah sakit.
 */
class Company extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'companies';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_uuid', 'uuid');
    }

    public function people()
    {
        return $this->hasMany(Person::class, 'company_uuid', 'uuid');
    }

    public function warehouses()
    {
        return $this->hasMany(Location::class, 'company_uuid', 'uuid');
    }

    public function masterProducts()
    {
        return $this->hasMany(MasterProduct::class, 'company_uuid', 'uuid');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'company_uuid', 'uuid');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'company_uuid', 'uuid');
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'company_uuid', 'uuid');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'company_uuid', 'uuid');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'company_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return CompanyFactory::new();
    }
}
