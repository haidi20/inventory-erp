<?php

namespace App\Modules\Operational\Master\Models;


use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use App\Modules\Operational\Master\Models\Product;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;

/**
 * Model ProductAccount
 *
 * Konfigurasi pemetaan akun G/L untuk transaksi penjualan, pembelian, dan persediaan produk.
 */
class ProductAccount extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'item_accounts';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }
}



