<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonInterface;

/**
 * Model GeneralLedger
 *
 * Buku besar utama yang merangkum seluruh mutasi debet/kredit per akun untuk laporan audit.
 */
class GeneralLedger extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'account_uuid',
        'journal_uuid',
        'journal_line_uuid',
        'journal_date',
        'reference_no',
        'description',
        'debit',
        'credit',
        'balance',
        'is_reconciled',
        'is_system',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_reconciled' => 'boolean',
        'is_system' => 'boolean',
        'debit' => 'double',
        'credit' => 'double',
        'balance' => 'double',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_uuid', 'uuid');
    }

    public function journalLine()
    {
        return $this->belongsTo(JournalLine::class, 'journal_line_uuid', 'uuid');
    }

    public function setJournalDateAttribute($value): void
    {
        $this->attributes['journal_date'] = $this->normalizeUnixTimestamp($value);
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
}




