<?php

namespace App\Modules\Operational\Manufacturing\Models;

use App\Modules\Operational\Master\Models\MasterProduct;
use App\Modules\Operational\Master\Models\Unit;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model BomDetail
 *
 * Daftar item komponen dan kuantitas standar yang didefinisikan dalam satu dokumen BOM.
 */
class BomDetail extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'bom_details';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(MasterProduct::class, 'item_uuid', 'uuid');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_uuid', 'uuid');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_uuid', 'uuid');
    }
}




