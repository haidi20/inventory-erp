<?php

namespace App\Modules\Operational\Master\Models;


use App\Shared\Traits\HasCompanyScope;use Database\Factories\LookupValueDetailFactory;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Modules\Core\Organization\Models\Company;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model LookupValueDetail
 *
 * Atribut tambahan atau metadata dari setiap nilai referensi lookup.
 */
class LookupValueDetail extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'lookup_value_details';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return LookupValueDetailFactory::new();
    }

    public function lookupValue()
    {
        return $this->belongsTo(LookupValue::class, 'lookup_value_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
}




