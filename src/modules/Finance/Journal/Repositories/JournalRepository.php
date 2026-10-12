<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\TransactionSequence;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * JournalRepository
 * 
 * Implementasi repository untuk manajemen Jurnal Akuntansi dan Buku Besar.
 * Menangani siklus hidup jurnal dari pembuatan hingga pemindahbukuan ke buku besar.
 */
class JournalRepository implements JournalRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findById(string $uuid): ?JournalEntity
    {
        $model = Journal::query()
            ->with('lines')
            ->where('uuid', $uuid)
            ->first();
        return $model ? JournalEntity::fromModel($model) : null;
    }

    /**
     * Cari jurnal tanpa global scope (company/branch). Dipakai service untuk
     * membedakan "jurnal ada tapi di luar scope user" (UnauthorizedException)
     * dari "jurnal tidak ada" (not found).
     */
    public function findByIdUnscoped(string $uuid): ?JournalEntity
    {
        $model = Journal::query()
            ->withoutGlobalScopes()
            ->with('lines')
            ->where('uuid', $uuid)
            ->first();
        return $model ? JournalEntity::fromModel($model) : null;
    }

    public function paginateForContext(string $branchUuid, string $companyUuid, int $perPage = 50): LengthAwarePaginator
    {
        return Journal::query()
            ->with('lines')
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->orderBy('journal_date', 'desc')
            ->paginate($perPage);
    }

    public function findForContext(string $uuid, string $branchUuid, string $companyUuid): ?JournalEntity
    {
        $journal = Journal::query()
            ->with('lines')
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $journal ? JournalEntity::fromModel($journal) : null;
    }

    public function referenceNoExists(string $referenceNo, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool
    {
        return Journal::query()
            ->where('reference_no', $referenceNo)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    public function getScheduledJournals(): array
    {
        return Journal::query()
            ->with('lines.account')
            ->where('status', 'unposted')
            ->where('workflow_status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now()->timestamp)
            ->where('is_processed', false)
            ->get()
            ->map(fn ($journal) => JournalEntity::fromModel($journal))
            ->all();
    }

    public function findScheduledForUpdate(string $uuid): ?JournalEntity
    {
        $journal = Journal::query()
            ->with('lines.account')
            ->where('uuid', $uuid)
            ->lockForUpdate()
            ->first();

        return $journal ? JournalEntity::fromModel($journal) : null;
    }

    public function accountExists(string $accountUuid, string $branchUuid, string $companyUuid): bool
    {
        return Account::query()
            ->where('uuid', $accountUuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function findByIdForUpdate(string $uuid): ?JournalEntity
    {
        $model = Journal::query()
            ->with('lines')
            ->where('uuid', $uuid)
            ->lockForUpdate()
            ->first();
        return $model ? JournalEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function findByReference(string $branchUuid, string $refType, string $refUuid): ?JournalEntity
    {
        $model = Journal::query()
            ->with('lines')
            ->where('branch_uuid', $branchUuid)
            ->where('reference_type', $refType)
            ->where('reference_uuid', $refUuid)
            ->first();
        return $model ? JournalEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function findByReferenceNo(string $refNo): ?JournalEntity
    {
        $model = Journal::query()
            ->with('lines')
            ->where('reference_no', $refNo)
            ->first();
        return $model ? JournalEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function findJournalByUuid(string $uuid): ?JournalEntity
    {
        return $this->findById($uuid);
    }

    /**
     * @inheritDoc
     */
    public function getReport(array $filters = []): Collection
    {
        return Journal::query()
            ->when(!empty($filters['branch_uuid']), fn ($query) => $query->where('branch_uuid', $filters['branch_uuid']))
            ->when(!empty($filters['date_from']), fn ($query) => $query->where('journal_date', '>=', (int) $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($query) => $query->where('journal_date', '<=', (int) $filters['date_to']))
            ->when(!empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['reference_type']), fn ($query) => $query->where('reference_type', $filters['reference_type']))
            ->orderBy('journal_date')
            ->orderBy('reference_no')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function createWithLines(JournalEntity $dto, ?string $actorUuid): JournalEntity
    {
        DB::beginTransaction();
        try {
            $journal = Journal::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $dto->branch_uuid,
                'transaction_code' => $dto->transaction_code,
                'reference_no' => $dto->reference_no,
                'reference_model' => $dto->reference_model,
                'reference_type' => $dto->reference_type,
                'reference_uuid' => $dto->reference_uuid,
                'closing_period_uuid' => $dto->closing_period_uuid,
                'journal_date' => $dto->journal_date,
                'scheduled_at' => $dto->scheduled_at,
                'description' => $dto->description,
                'status' => $dto->status,
                'reversal_status' => $dto->reversal_status,
                'reversal_date' => $dto->reversal_date,
                'reversal_journal_uuid' => $dto->reversal_journal_uuid,
                'workflow_status' => $dto->workflow_status,
                'is_system' => $dto->is_system,
                'is_adjusting' => $dto->is_adjusting,
                'is_processed' => $dto->is_processed,
                'version' => $dto->version,
                'created_by' => $actorUuid,
                'updated_by' => $actorUuid,
            ]);

            foreach ($dto->lines as $line) {
                $journal->lines()->create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $line->branch_uuid ?? $journal->branch_uuid,
                    'account_uuid' => $line->account_uuid,
                    'contact_uuid' => $line->contact_uuid,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'foreign_currency' => $line->foreign_currency,
                    'foreign_amount' => $line->foreign_amount,
                    'exchange_rate' => $line->exchange_rate,
                    'description' => $line->description,
                    'is_fiscal' => $line->is_fiscal,
                    'fiscal_type' => $line->fiscal_type,
                ]);
            }

            DB::commit();
            return JournalEntity::fromModel($journal->load('lines.account'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function createManyWithLines(array $dtos, string $actorUuid): array
    {
        DB::beginTransaction();
        try {
            $results = [];
            foreach ($dtos as $dto) {
                $results[] = $this->createWithLines($dto, $actorUuid);
            }
            DB::commit();
            return $results;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function upsertJournal(JournalEntity $dto): JournalEntity
    {
        DB::beginTransaction();
        try {
            $existing = Journal::query()->where('reference_no', $dto->reference_no)->first();
            
            if ($existing) {
                $existing->update([
                    'transaction_code' => $dto->transaction_code,
                    'journal_date' => $dto->journal_date,
                    'description' => $dto->description,
                    'status' => $dto->status,
                    'reversal_status' => $dto->reversal_status,
                    'reversal_date' => $dto->reversal_date,
                    'reversal_journal_uuid' => $dto->reversal_journal_uuid,
                    'workflow_status' => $dto->workflow_status,
                    'version' => $dto->version,
                    'updated_at' => time(),
                ]);

                // Re-create lines
                $existing->lines()->delete();
                foreach ($dto->lines as $line) {
                    $existing->lines()->create([
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $line->branch_uuid ?? $existing->branch_uuid,
                        'account_uuid' => $line->account_uuid,
                        'contact_uuid' => $line->contact_uuid,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'foreign_currency' => $line->foreign_currency,
                        'foreign_amount' => $line->foreign_amount,
                        'exchange_rate' => $line->exchange_rate,
                        'description' => $line->description,
                        'is_fiscal' => $line->is_fiscal,
                        'fiscal_type' => $line->fiscal_type,
                    ]);
                }
                DB::commit();
                return JournalEntity::fromModel($existing->load('lines'));
            }

            DB::commit();
            return $this->createWithLines($dto, null);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateWithStatus(string $uuid, array $data): JournalEntity
    {
        $journal = Journal::query()->where('uuid', $uuid)->first();
        if (!$journal) throw new Exception("Journal not found.");
        
        $journal->update($data);
        return JournalEntity::fromModel($journal->load('lines.account'));
    }

    /**
     * @inheritDoc
     */
    public function reverseJournal(string $uuid, JournalEntity $reversalDto, array $updateOriginalData): JournalEntity
    {
        DB::beginTransaction();
        try {
            $original = Journal::query()->where('uuid', $uuid)->first();
            if (!$original) throw new Exception("Original journal not found.");

            $original->update($updateOriginalData);
            
            $reversal = $this->createWithLines($reversalDto, null);
            
            $original->update(['reversal_journal_uuid' => $reversal->uuid, 'reversal_status' => 'reversed']);
            
            DB::commit();
            return $reversal;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function bulkCreateLedgerEntries(array $entries): void
    {
        if (empty($entries)) return;

        $preparedEntries = array_map(function ($e) {
            return array_merge($e, [
                'uuid' => (string) Str::uuid(),
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }, $entries);

         GeneralLedger::query()->insert($preparedEntries);
    }

    /**
     * @inheritDoc
     */
    public function getJournalLines(string $journalUuid): array
    {
        return JournalLine::query()
            ->where('journal_uuid', $journalUuid)
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function updateStatus(string $uuid, string $status, string $actorUuid): void
    {
        Journal::query()
            ->where('uuid', $uuid)
            ->update([
                'status' => $status,
                'updated_by' => $actorUuid,
                'updated_at' => time(),
            ]);
    }

    /**
     * @inheritDoc
     */
    public function deleteWithTransaction(string $uuid): bool
    {
        DB::beginTransaction();
        try {
            $journal = Journal::query()->where('uuid', $uuid)->first();
            if (!$journal) return false;

            // Delete associated lines and ledger entries
            $journal->lines()->delete();
            GeneralLedger::query()->where('journal_uuid', $uuid)->delete();
            
            DB::commit();
            return $journal->delete();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid, string $actorUuid): void
    {
        $this->deleteWithTransaction($uuid);
    }

    /**
     * @inheritDoc
     */
    public function markAsProcessed(string $uuid): void
    {
        Journal::query()->where('uuid', $uuid)->update(['is_processed' => true]);
        JournalLine::query()->where('journal_uuid', $uuid)->update(['is_processed' => true]);
    }

    /**
     * @inheritDoc
     */
    public function getNextSequence(string $branchUuid, string $companyUuid, string $prefix, string $period, string $actorUuid): int
    {
        // The migration column is `type_code`; treat the $prefix arg as the type code.
        DB::beginTransaction();
        try {
            $seq = TransactionSequence::query()
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->where('type_code', $prefix)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (!$seq) {
                $seq = TransactionSequence::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'type_code' => $prefix,
                    'period' => $period,
                    'last_index' => 1,
                ]);
                DB::commit();
                return 1;
            }

            $seq->last_index++;
            $seq->save();

            DB::commit();
            return $seq->last_index;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
