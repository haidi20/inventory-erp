<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model PersonSaldo
 *
 * Ringkasan saldo aktif (hutang/piutang) untuk monitoring kesehatan finansial terhadap mitra.
 */
class PersonSaldo extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'person_saldo_details';

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

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Location::class, 'location_uuid', 'uuid');
    }
}




