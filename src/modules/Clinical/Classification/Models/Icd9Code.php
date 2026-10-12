<?php

namespace App\Modules\Clinical\Classification\Models;

use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Icd9Code extends Model implements AuditableContract
{
    use Auditable, Blameable, HasSoftDeleteUnique, HasUuids;

    protected $table = 'icd9_codes';

    protected $dateFormat = 'U';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'code',
        'name_uuid',
        'name_en',
        'category',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
