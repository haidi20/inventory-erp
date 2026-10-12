<?php

namespace App\Modules\Finance\Journal\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Modules\Core\Organization\Models\Branch;
use Carbon\CarbonInterface;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Database\Factories\JournalFactory;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Models\JournalAttachment;


/**
 * @property string|null $reversal_journal_uuid
 */
class Journal extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;

    protected $table = 'journals';
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = [
        'status' => JournalStatus::class,
        'workflow_status' => JournalWorkflowStatus::class,
    ];

    /** @return HasMany<JournalLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'journal_uuid', 'uuid');
    }

    public function attachments()
    {
        return $this->hasMany(JournalAttachment::class, 'journal_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function closingPeriod()
    {
        return $this->belongsTo(ClosingPeriod::class, 'closing_period_uuid', 'uuid');
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

    public function getReversalJournalIdAttribute(): ?string
    {
        return $this->reversal_journal_uuid;
    }

    public function setJournalDateAttribute($value): void
    {
        $this->attributes['journal_date'] = $this->normalizeUnixTimestamp($value);
    }

    public function setScheduledAtAttribute($value): void
    {
        $this->attributes['scheduled_at'] = $value === null ? null : $this->normalizeUnixTimestamp($value);
    }

    public function setReversalDateAttribute($value): void
    {
        $this->attributes['reversal_date'] = $value === null ? null : $this->normalizeUnixTimestamp($value);
    }

    private function normalizeUnixTimestamp($value): int
    {
        if ($value instanceof CarbonInterface) {
            return $value->timestamp;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return strtotime((string) $value) ?: time();
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return JournalFactory::new();
    }
}
