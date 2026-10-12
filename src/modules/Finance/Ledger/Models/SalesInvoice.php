<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PaymentTerm;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Operational\SalesInvoiceFactory;

/**
 * Model SalesInvoice
 *
 * Faktur resmi yang diterbitkan sebagai bukti tagihan dan dasar pencatatan piutang.
 */
class SalesInvoice extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $table = 'sales_invoices';

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

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_uuid', 'uuid');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(SalesInvoiceDetail::class, 'sales_invoice_uuid', 'uuid');
    }

    public function agings()
    {
        return $this->hasMany(SalesInvoiceAging::class, 'sales_invoice_uuid', 'uuid');
    }

    protected static function newFactory()
    {
        return SalesInvoiceFactory::new();
    }
}





