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
 * Model TempLookupDetail
 *
 * Rincian data referensi sementara dalam proses staging data.
 */
class TempLookupDetail extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'temp_lov_details';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function tempLookup()
    {
        return $this->belongsTo(TempLookup::class, 'temp_lov_uuid', 'uuid');
    }
}




