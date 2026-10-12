<?php

namespace App\Modules\Clinical\Integration\Models;

use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SatusehatSetting extends Model
{
    use Blameable, HasUuids, HasSoftDeleteUnique;

    protected $dateFormat = 'U';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'satusehat_settings';

    protected $fillable = [
        'uuid',
        'org_uuid',
        'client_uuid',
        'client_secret',
        'base_url',
        'auth_url',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}