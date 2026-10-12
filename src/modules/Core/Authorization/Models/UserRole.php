<?php

namespace App\Modules\Core\Authorization\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model UserRole
 *
 * Penugasan satu atau lebih peran (Role) kepada pengguna sistem tertentu.
 */
class UserRole extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'user_roles';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}



