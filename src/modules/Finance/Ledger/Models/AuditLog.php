<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Shared\Traits\Blameable;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Core\Identity\Models\User;

/**
 * Model AuditLog
 *
 * Jejak audit otomatis yang mencatat setiap aktivitas CRUD pada data sensitif di sistem.
 */
class AuditLog extends Model {
    protected $dateFormat = 'U';
    
    use Blameable, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'user_uuid',
        'event',
        'entity_type',
        'entity_uuid',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}





