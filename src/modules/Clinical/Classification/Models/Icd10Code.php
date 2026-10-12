<?php

namespace App\Modules\Clinical\Classification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Icd10Code
 *
 * Referensi standar internasional pengkodean diagnosis penyakit (ICD-10) untuk klaim dan medis.
 */
class Icd10Code extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'code',
        'name_uuid',
        'name_en',
        'category',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}



