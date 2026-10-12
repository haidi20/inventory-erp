<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\AdjustmentEntity;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Models\Journal;

class AccountingCycleRepository
{
    public function createPeriod(string $period): ClosingPeriod
    {
        return ClosingPeriod::firstOrCreate(
            ['period' => $period],
            ['status' => 'open']
        );
    }

    public function activatePeriod(string $uuid): void
    {
        ClosingPeriod::where('status', 'open')->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        ClosingPeriod::where('uuid', $uuid)->first()?->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    public function deletePeriod(string $uuid): void
    {
        ClosingPeriod::where('uuid', $uuid)->firstOrFail()->delete();
    }

    public function periodExistsForCompany(string $uuid, string $companyUuid): bool
    {
        return ClosingPeriod::withoutGlobalScopes()
            ->where('uuid', $uuid)
            ->where('company_uuid', $companyUuid)
            ->exists();
    }

    public function generateAdjustingJournals(string $periodUuid, ?string $branchUuid = null, bool $autoReverse = true)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        return DB::select("CALL sp_generate_adjusting_journals(?, ?, ?)", [$periodUuid, $branchUuid, $autoReverse]);
    }

    public function generateClosingJournals(string $periodUuid, ?string $branchUuid = null)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        return DB::select("CALL sp_generate_closing_journals(?, ?)", [$periodUuid, $branchUuid]);
    }

    public function getPostClosingTrialBalance(string $periodUuid, ?string $branchUuid = null)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        return DB::select("CALL sp_get_post_closing_trial_balance(?, ?)", [$periodUuid, $branchUuid]);
    }

    public function initializeOpeningBalance(string $fromPeriodUuid, string $toPeriodUuid, ?string $branchUuid = null)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        return DB::select("CALL sp_initialize_opening_balance(?, ?, ?)", [$fromPeriodUuid, $toPeriodUuid, $branchUuid]);
    }

    public function validatePeriodForAdjustment(string $periodUuid, ?string $branchUuid = null)
    {
        // Use withoutGlobalScopes to ensure we find the global period record
        $period = ClosingPeriod::withoutGlobalScopes()->where('uuid', $periodUuid)->firstOrFail();
        
        if ($period->status === 'closed') {
            throw new \Exception("Cannot generate adjusting journals for a closed period.");
        }

        // Idempotency check: Check if ADJ journals already exist for this period/branch
        $exists = Journal::where('branch_uuid', $branchUuid)
            ->where('is_adjusting', true)
            ->whereBetween('journal_date', [$period->start_date, $period->end_date])
            ->exists();
            
        if ($exists) {
            throw new \Exception("Adjusting journals for this period already exist.");
        }

        return $period;
    }

    public function createAdjustingJournal(AdjustmentEntity|array $data)
    {
        $data = $data instanceof AdjustmentEntity ? $data->toArray() : $data;

        DB::beginTransaction();
        try {
            $journal = Journal::create([
                'branch_uuid' => $data['branch_uuid'],
                'transaction_code' => 'ADJ-' . strtoupper(uniqid()),
                'reference_no' => 'REF-ADJ-' . strtoupper(uniqid()),
                'journal_date' => time(),
                'description' => $data['description'],
                'type' => 'ADJ',
                'reference_type' => 'adjustment',
                'status' => 'posted',
                'is_adjusting' => true
            ]);

            $journal->lines()->create([
                'branch_uuid' => $data['branch_uuid'],
                'account_uuid' => $data['debit_account_uuid'],
                'debit' => $data['amount'],
                'credit' => 0
            ]);

            $journal->lines()->create([
                'branch_uuid' => $data['branch_uuid'],
                'account_uuid' => $data['credit_account_uuid'],
                'debit' => 0,
                'credit' => $data['amount']
            ]);

            DB::commit();
            return $journal;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCashFlowIndirect(string $periodUuid, ?string $branchUuid = null)
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        return DB::select("CALL sp_get_cash_flow_indirect(?, ?)", [$periodUuid, $branchUuid]);
    }
}

