<?php

namespace App\Modules\Operational\Master\Models;


use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model ItemUom
 *
 * Unit of Measure; definisi satuan ukur barang yang digunakan dalam seluruh modul sistem.
 */
class ItemUom extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'item_uoms';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(MasterProduct::class, 'item_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
}




