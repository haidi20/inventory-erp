<?php

namespace App\Modules\Operational\Master\Models;

use Database\Factories\LookupSetFactory;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;


/**
 * Model LookupSet
 *
 * Grup kategori untuk data referensi lookup yang dapat dikonfigurasi secara dinamis.
 */
class LookupSet extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'lookup_sets';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return LookupSetFactory::new();
    }

    public function values()
    {
        return $this->hasMany(LookupValue::class, 'lookup_set_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
}




