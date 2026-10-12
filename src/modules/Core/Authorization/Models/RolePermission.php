<?php

namespace App\Modules\Core\Authorization\Models;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model RolePermission
 *
 * Tabel relasi yang menghubungkan Permission tertentu ke dalam suatu Role.
 */
class RolePermission extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'role_permissions';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_uuid', 'uuid');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_uuid', 'uuid');
    }
}



