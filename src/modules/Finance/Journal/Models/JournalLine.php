<?php

namespace App\Modules\Finance\Journal\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Modules\Core\Organization\Models\Branch;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Database\Factories\JournalLineFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasUuids, HasSoftDeleteUnique, Auditable, Blameable, HasCompanyScope, HasBranchScope;

    protected $table = 'journal_lines';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_uuid', 'uuid');
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'uuid');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return JournalLineFactory::new();
    }
}
