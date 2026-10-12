<?php

namespace App\Modules\Operational\Master\Models;


use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\HasCompanyScope;use Database\Factories\LookupTypeFactory;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model LookupType
 *
 * Kategori utama untuk data pilihan (dropdown) standar di seluruh aplikasi.
 */
class LookupType extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'lookup_types';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return LookupTypeFactory::new();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
}



