<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use App\Modules\Operational\Master\Models\PersonCustomField;
use Database\Factories\Master\PersonFactory;

/**
 * Model Person
 *
 * Entitas universal untuk pihak ketiga (supplier, customer, personil) agar data kontak terdesentralisasi.
 */
class Person extends Model implements AuditableContract {
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

    public function addresses()
    {
        return $this->hasMany(PersonAddress::class, 'person_uuid', 'uuid');
    }

    public function contacts()
    {
        return $this->hasMany(PersonContact::class, 'person_uuid', 'uuid');
    }

    public function customFields()
    {
        return $this->hasMany(PersonCustomField::class, 'person_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return PersonFactory::new();
    }
}
