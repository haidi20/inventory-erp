<?php

namespace App\Modules\Clinical\Integration\Models;

use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BpjsSetting extends Model
{
    use Blameable, HasSoftDeleteUnique, HasUuids;

    protected $dateFormat = 'U';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'bpjs_settings';

    protected $fillable = [
        'base_url',
        'consumer_uuid',
        'user_key',
        'secret_key',
        'ppk_code',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
