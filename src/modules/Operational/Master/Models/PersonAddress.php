<?php

namespace App\Modules\Operational\Master\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Master\PersonAddressFactory;

/**
 * Model PersonAddress
 *
 * Metadata alamat lengkap untuk entitas Person (kantor, rumah, gudang pengiriman).
 */
class PersonAddress extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'person_addresses';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return PersonAddressFactory::new();
    }
}
