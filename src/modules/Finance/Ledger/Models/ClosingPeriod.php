<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Finance\Journal\Enums\AccountType;
use Database\Factories\ClosingPeriodFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Str;

/**
 * Model ClosingPeriod
 *
 * Mengatur periode penutupan buku untuk mencegah perubahan data pada periode yang sudah final secara akuntansi.
 */
class ClosingPeriod extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'period',
        'name',
        'start_date',
        'end_date',
        'closed_at',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
    ];

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

    public function setStartDateAttribute($value): void
    {
        $this->attributes['start_date'] = is_numeric($value) ? (int) $value : strtotime((string) $value);
    }

    public function setEndDateAttribute($value): void
    {
        $this->attributes['end_date'] = is_numeric($value) ? (int) $value : strtotime((string) $value);
    }

    protected static function booted()
    {
        static::creating(function ($period) {
            $lastPeriod = static::where('branch_uuid', $period->branch_uuid)
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastPeriod) {
                // ... same overlap logic ...
                $lastEnd = Carbon::parse($lastPeriod->end_date);
                $newStart = Carbon::parse($period->start_date);

                if ($newStart->lte($lastEnd)) {
                    throw new \Exception('Closing period dates overlap with an existing period.');
                }

                $expectedStart = $lastEnd->copy()->addDay()->toDateString();
                if ($newStart->toDateString() !== $expectedStart) {
                    throw new \Exception('Cannot create closing period. There is a gap from the previous period.');
                }
            }
        });

        static::created(function ($period) {
            $prevPeriod = static::where('branch_uuid', $period->branch_uuid)
                ->where('end_date', '<', $period->start_date)
                ->orderBy('end_date', 'desc')
                ->first();

            if ($prevPeriod) {
                $balances = AccountBalance::with('account')
                    ->where('branch_uuid', $period->branch_uuid)
                    ->where('period', $prevPeriod->period)
                    ->get();

                foreach ($balances as $b) {
                    if (! $b->account) {
                        continue;
                    }

                    $accountType = $b->account->type instanceof AccountType
                        ? $b->account->type->value
                        : (string) $b->account->type;

                    $isBalanceSheet = in_array($accountType, [
                        AccountType::ASSET->value,
                        AccountType::LIABILITY->value,
                        AccountType::EQUITY->value,
                    ], true);

                    AccountBalance::create([
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $period->branch_uuid,
                        'account_uuid' => $b->account_uuid,
                        'period' => $period->period,
                        'opening_balance' => $isBalanceSheet ? $b->closing_balance : 0,
                        'total_debit' => 0,
                        'total_credit' => 0,
                        'closing_balance' => $isBalanceSheet ? $b->closing_balance : 0,
                    ]);
                }
            }
        });
    }
    protected static function newFactory(): Factory
    {
        return ClosingPeriodFactory::new();
    }
}
