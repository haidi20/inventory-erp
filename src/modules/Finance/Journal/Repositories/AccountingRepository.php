<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\CoaEntity;
use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Entities\AssetEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Asset;
use App\Modules\Finance\Journal\Models\Coa;
use App\Modules\Finance\Journal\Models\CoaSetup;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * AccountingRepository
 * 
 * Implementasi repository untuk manajemen data Akuntansi, COA, dan Aset.
 * Mengatur logika penyimpanan dan pencarian data keuangan.
 */
class AccountingRepository implements AccountingRepositoryInterface
{
    // --- Account Methods ---

    /** @inheritDoc */
    public function createAccount(AccountEntity $entity): AccountEntity
    {
        $model = Account::query()->create([
            'uuid' => $entity->uuid ?? (string) Str::uuid(),
            'code' => trim($entity->code),
            'name' => trim($entity->name),
            'type' => $entity->type,
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid,
        ]);
        return AccountEntity::fromModel($model);
    }

    /** @inheritDoc */
    public function updateAccount(AccountEntity $entity): AccountEntity
    {
        $acc = Account::query()->findOrFail(trim($entity->uuid));
        $acc->update([
            'code' => trim($entity->code),
            'name' => trim($entity->name),
            'type' => $entity->type,
        ]);
        return AccountEntity::fromModel($acc);
    }

    /** @inheritDoc */
    public function deleteAccount(string $uuid): void
    {
        Account::query()->where('uuid', trim($uuid))->firstOrFail()->delete();
    }

    /** @inheritDoc */
    public function getAccountByUuid(string $uuid): ?AccountEntity
    {
        $model = Account::query()->where('uuid', trim($uuid))->first();
        return $model ? AccountEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getAccountByCode(string $code): ?AccountEntity
    {
        $model = Account::query()->where('code', trim($code))->first();
        return $model ? AccountEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getAllAccounts(): array
    {
        $items = Account::query()->orderBy('code', 'ASC')->get();
        return $items->map(fn($m) => AccountEntity::fromModel($m))->toArray();
    }

    // --- Coa Methods ---

    /** @inheritDoc */
    public function createCoa(CoaEntity $entity): CoaEntity
    {
        $model = Coa::query()->create([
            'uuid' => $entity->uuid ?? (string) Str::uuid(),
            'code' => trim($entity->code),
            'name' => trim($entity->name),
            'account_uuid' => $this->getCoaAttribute($entity, 'account_uuid') ?? (string) Str::uuid(),
            'branch_uuid' => $this->getCoaAttribute($entity, 'branch_uuid') ?? (string) Str::uuid(),
            'company_uuid' => $this->getCoaAttribute($entity, 'company_uuid') ?? (string) Str::uuid(),
            'is_active' => true,
        ]);
        return CoaEntity::fromModel($model);
    }

    /** @inheritDoc */
    public function updateCoa(CoaEntity $entity): CoaEntity
    {
        $coa = Coa::query()->findOrFail(trim($entity->uuid));
        $payload = [
            'code' => trim($entity->code),
            'name' => trim($entity->name),
        ];

        $accountUuid = $this->getCoaAttribute($entity, 'account_uuid');
        $branchUuid = $this->getCoaAttribute($entity, 'branch_uuid');
        $companyUuid = $this->getCoaAttribute($entity, 'company_uuid');

        if ($accountUuid !== null) {
            $payload['account_uuid'] = $accountUuid;
        }

        if ($branchUuid !== null) {
            $payload['branch_uuid'] = $branchUuid;
        }

        if ($companyUuid !== null) {
            $payload['company_uuid'] = $companyUuid;
        }

        $coa->update($payload);
        return CoaEntity::fromModel($coa);
    }

    /** @inheritDoc */
    public function deleteCoa(string $uuid): void
    {
        Coa::query()->where('uuid', trim($uuid))->firstOrFail()->delete();
    }

    /** @inheritDoc */
    public function getCoaByUuid(string $uuid): ?CoaEntity
    {
        $model = Coa::query()->where('uuid', trim($uuid))->first();
        return $model ? CoaEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getAllCoas(): array
    {
        $items = Coa::query()->orderBy('code', 'ASC')->get();
        return $items->map(fn($m) => CoaEntity::fromModel($m))->toArray();
    }

    // --- Asset Methods ---

    /** @inheritDoc */
    public function createAsset(AssetEntity $entity): AssetEntity
    {
        $model = Asset::query()->create([
            'uuid' => $entity->uuid ?? (string) Str::uuid(),
            'code' => trim($entity->code),
            'name' => trim($entity->name),
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid ?? (string) Str::uuid(),
            'asset_value' => $entity->asset_value ?? 0,
            'life_year' => $entity->life_year,
            'depreciation_method_uuid' => $entity->depreciation_method_uuid,
            'yearly_depreciation' => $entity->yearly_depreciation ?? 0,
        ]);
        return AssetEntity::fromModel($model);
    }

    /** @inheritDoc */
    public function updateAsset(AssetEntity $entity): AssetEntity
    {
        $asset = Asset::query()->findOrFail(trim($entity->uuid));
        $asset->update([
            'code' => trim($entity->code),
            'name' => trim($entity->name),
            'branch_uuid' => $entity->branch_uuid,
            'company_uuid' => $entity->company_uuid,
            'asset_value' => $entity->asset_value ?? 0,
            'life_year' => $entity->life_year,
            'depreciation_method_uuid' => $entity->depreciation_method_uuid,
            'yearly_depreciation' => $entity->yearly_depreciation ?? 0,
        ]);
        return AssetEntity::fromModel($asset);
    }

    /** @inheritDoc */
    public function deleteAsset(string $uuid): void
    {
        Asset::query()->where('uuid', trim($uuid))->firstOrFail()->delete();
    }

    /** @inheritDoc */
    public function getAssetByUuid(string $uuid): ?AssetEntity
    {
        $model = Asset::query()->where('uuid', trim($uuid))->first();
        return $model ? AssetEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getAssetByCode(string $code): ?AssetEntity
    {
        $model = Asset::query()->where('code', trim($code))->first();
        return $model ? AssetEntity::fromModel($model) : null;
    }

    /** @inheritDoc */
    public function getAllAssets(AssetEntity $entity): array
    {
        $query = Asset::query();

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(code) LIKE ?", ["%{$search}%"]);
            });
        }

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        $total = $query->count();
        $items = $query->orderBy('code', 'ASC')
            ->offset(($entity->page - 1) * $entity->per_page)
            ->limit($entity->per_page)
            ->get();

        return [
            'items' => $items->map(fn($m) => AssetEntity::fromModel($m))->toArray(),
            'total' => $total,
        ];
    }

    // --- Coa Setup Methods ---

    /** @inheritDoc */
    public function updateCoaSetup(array $data): CoaSetup
    {
        $normalizedData = $this->normalizeCoaSetupData($data);

        $setup = CoaSetup::query()->where('program_account', $normalizedData['program_account'])->first();
        if ($setup) {
            $setup->update($normalizedData);
        } else {
            $setup = CoaSetup::query()->create($normalizedData);
        }
        return $setup;
    }

    /** @inheritDoc */
    public function getCoaSetupByProgram(string $program): ?CoaSetup
    {
        return CoaSetup::query()->where('program_account', trim($program))->first();
    }

    // --- Accounting Cycle ---

    /** @inheritDoc */
    public function syncGeneralLedgerByPeriod(string $periodUuid): void
    {
        $periodFound = ClosingPeriod::query()->where('uuid', $periodUuid)->firstOrFail();
        
        DB::beginTransaction();
        try {
            // 1. Cleanup Stale records
            GeneralLedger::query()
                ->whereBetween('journal_date', [$periodFound->start_date, $periodFound->end_date])
                ->where('branch_uuid', $periodFound->branch_uuid)
                ->whereNotIn('journal_line_uuid', function($query) use ($periodFound) {
                    $query->select('jl.uuid')
                        ->from('journal_lines as jl')
                        ->join('journals as j', 'jl.journal_uuid', '=', 'j.uuid')
                        ->whereBetween('j.journal_date', [$periodFound->start_date, $periodFound->end_date])
                        ->whereNull('jl.deleted_at')
                        ->whereNull('j.deleted_at')
                        ->whereIn('j.status', ['posted', 'voided']);
                })->delete();

            // 2. Fetch Journal Lines for Upsert
            $lines = JournalLine::query()
                ->join('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
                ->whereBetween('journals.journal_date', [$periodFound->start_date, $periodFound->end_date])
                ->where('journals.branch_uuid', $periodFound->branch_uuid)
                ->whereIn('journals.status', ['posted', 'voided'])
                ->select([
                    'journal_lines.*',
                    'journals.journal_date',
                    'journals.reference_no',
                    'journals.description as journal_desc',
                    'journals.is_system'
                ])->get();

            foreach ($lines as $line) {
                GeneralLedger::query()->updateOrCreate(
                    ['journal_line_uuid' => $line->uuid],
                    [
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $line->branch_uuid,
                        'account_uuid' => $line->account_uuid,
                        'journal_uuid' => $line->journal_uuid,
                        'journal_date' => $line->journal_date,
                        'reference_no' => $line->reference_no,
                        'description' => $line->description ?: $line->journal_desc,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'is_reconciled' => 0,
                        'is_system' => $line->is_system
                    ]
                );
            }

            // 3. Recalculate Running Balance
            $this->recalculateRunningBalances($periodFound->branch_uuid, $periodFound->period, $periodFound->start_date, $periodFound->end_date);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Helper to recalculate running balances for a specific branch and period.
     */
    protected function recalculateRunningBalances(string $branchUuid, string $periodStr, $startDate, $endDate): void
    {
        // Get all accounts that have ledger entries in this period
        $accountUuids = GeneralLedger::query()
            ->where('branch_uuid', $branchUuid)
            ->whereBetween('journal_date', [$startDate, $endDate])
            ->distinct()
            ->pluck('account_uuid');

        foreach ($accountUuids as $accountUuid) {
            // Get opening balance for this account in this period
            $opening = AccountBalance::query()
                ->where('branch_uuid', $branchUuid)
                ->where('account_uuid', $accountUuid)
                ->where('period', $periodStr)
                ->value('opening_balance') ?: 0;

            // Fetch entries ordered by date and uuid (deterministic sequence)
            $entries = GeneralLedger::query()
                ->where('branch_uuid', $branchUuid)
                ->where('account_uuid', $accountUuid)
                ->whereBetween('journal_date', [$startDate, $endDate])
                ->orderBy('journal_date', 'ASC')
                ->orderBy('uuid', 'ASC')
                ->get();

            $currentBalance = $opening;
            foreach ($entries as $entry) {
                // Simplified balance calculation: for Assets/Expenses (Debit normal), balance = balance + debit - credit
                // However, many systems use absolute balance for GL. 
                // We'll follow the old SP logic: running_sum = opening + SUM(debit - credit)
                $currentBalance += ($entry->debit - $entry->credit);
                $entry->update(['balance' => $currentBalance]);
            }
        }
    }

    /** @inheritDoc */
    public function generateAdjustingJournals(string $periodUuid, string $branchUuid, bool $autoReverse): string
    {
        // Placeholder for real logic (Depreciation, Prepaid, etc)
        return "Adjusting journals successfully logic simulated.";
    }

    /** @inheritDoc */
    public function generateClosingJournals(string $periodUuid, string $branchUuid): string
    {
        // Placeholder for closing entries to Retained Earnings
        return "Closing journals successfully logic simulated.";
    }

    /** @inheritDoc */
    public function initializeOpeningBalance(string $fromPeriodUuid, string $toPeriodUuid, string $branchUuid): string
    {
        $fromPeriod = ClosingPeriod::query()->where('uuid', $fromPeriodUuid)->firstOrFail();
        $toPeriod = ClosingPeriod::query()->where('uuid', $toPeriodUuid)->firstOrFail();

        DB::beginTransaction();
        try {
            // Fetch closing balances from 'from' period
            $balances = GeneralLedger::query()
                ->where('branch_uuid', $branchUuid)
                ->where('journal_date', '<=', $fromPeriod->end_date)
                ->select('account_uuid', DB::raw('SUM(debit - credit) as final_balance'))
                ->groupBy('account_uuid')
                ->get();

            foreach ($balances as $bal) {
                AccountBalance::query()->updateOrCreate(
                    [
                        'branch_uuid' => $branchUuid,
                        'account_uuid' => $bal->account_uuid,
                        'period' => $toPeriod->period
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'opening_balance' => $bal->final_balance,
                        'closing_balance' => $bal->final_balance, // initial snapshot
                        'total_debit' => 0,
                        'total_credit' => 0
                    ]
                );
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        };

        return "Opening Balance successfully carried forward to " . $toPeriod->period;
    }

    private function getCoaAttribute(CoaEntity $entity, string $attribute): ?string
    {
        return property_exists($entity, $attribute) ? $entity->{$attribute} : null;
    }

    private function normalizeCoaSetupData(array $data): array
    {
        $programAccount = $data['program_account'] ?? $data['program_name'] ?? null;

        return [
            'program_account' => $programAccount,
            'coa_uuid' => $data['coa_uuid'] ?? null,
            'account_uuid' => $data['account_uuid'] ?? null,
            'note' => $data['note'] ?? null,
            'company_uuid' => $data['company_uuid'] ?? null,
            'branch_uuid' => $data['branch_uuid'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
            'deleted_by' => $data['deleted_by'] ?? null,
        ];
    }
}
