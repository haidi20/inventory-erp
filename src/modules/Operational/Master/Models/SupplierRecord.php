<?php

namespace App\Modules\Operational\Master\Models;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model SupplierRecord
 *
 * Catatan performa, histori transaksi, dan evaluasi periodik terhadap pemasok tertentu.
 */
class SupplierRecord extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'people';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_uuid', 'uuid');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function priceLevel()
    {
        return $this->belongsTo(PriceLevel::class, 'price_level_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }
}




