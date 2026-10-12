<?php

namespace App\Modules\Operational\Master\Models;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model TempLookup
 *
 * Tabel penampung sementara untuk data referensi selama proses integrasi atau migrasi.
 */
class TempLookup extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'temp_lovs';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function lookupSet()
    {
        return $this->belongsTo(LookupSet::class, 'lookup_set_uuid', 'uuid');
    }

    public function lookupValue()
    {
        return $this->belongsTo(LookupValue::class, 'lookup_value_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(TempLookupDetail::class, 'temp_lov_uuid', 'uuid');
    }
}




