<?php

namespace App\Modules\Core\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Shared\Traits\HasSoftDeleteUnique;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Model Permission
 *
 * Hak akses spesifik (gate/ability) yang dipetakan ke peran tertentu dalam sistem keamanan.
 */
class Permission extends SpatiePermission implements AuditableContract {
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique;


    protected $dateFormat = 'U';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'id',
        'name',
        'label',
        'guard_name',
    ];

    /**
     * Override findByName untuk mendukung deleted_at = 0.
     */
    public static function findByName(string $name, $guardName = null): \Spatie\Permission\Contracts\Permission
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }
}


