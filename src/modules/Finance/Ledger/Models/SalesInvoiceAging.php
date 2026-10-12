<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model SalesInvoiceAging
 *
 * Analisis umur piutang faktur untuk memonitor keterlambatan pembayaran pelanggan.
 */
class SalesInvoiceAging extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $table = 'sales_invoice_agings';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_uuid', 'uuid');
    }
}





