<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Modules\Finance\Journal\Models\Account;
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
 * Model PurchaseReturnPayment
 *
 * Pencatatan pengembalian dana atau kompensasi hutang atas transaksi retur pembelian.
 */
class PurchaseReturnPayment extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $table = 'purchase_return_payments';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_uuid', 'uuid');
    }
}




