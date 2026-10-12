<?php

namespace App\Modules\Finance\Journal\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

use App\Modules\Core\Organization\Models\Branch;

/**
 * Model RecurringJournal
 *
 * Template untuk pembuatan jurnal akuntansi otomatis yang terjadi secara rutin/periodik.
 */
class RecurringJournal extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'template_name',
        'frequency',
        'next_run_date',
        'last_run_date',
        'end_date',
        'journal_data',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'journal_data' => 'array',
        'next_run_date' => 'datetime',
        'last_run_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }
}




