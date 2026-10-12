<?php

namespace App\Modules\Operational\Master\Models;


use App\Shared\Traits\HasCompanyScope;use Database\Factories\LookupValueFactory;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model LookupValue
 *
 * Nilai spesifik dalam grup lookup yang digunakan sebagai pilihan data standar.
 */
class LookupValue extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'lookup_values';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return LookupValueFactory::new();
    }

    public function lookupSet()
    {
        return $this->belongsTo(LookupSet::class, 'lookup_set_uuid', 'uuid');
    }
    public function details()
    {
        return $this->hasMany(LookupValueDetail::class, 'lookup_value_uuid', 'uuid');
    }
}




