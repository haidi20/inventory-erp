<?php

namespace App\Modules\Finance\Ledger\Repositories;

use App\Modules\Finance\Ledger\Entities\AccountBalanceEntity;
use App\Modules\Finance\Ledger\Entities\TrialBalanceEntity;
use App\Modules\Finance\Journal\Entities\PeriodInfoEntity;
use App\Modules\Finance\Journal\Entities\TotalsEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Repository untuk laporan Neraca Saldo (Trial Balance).
 * Menggunakan migrasi:
 * - 2026_03_15_000500_create_accounting_tables.php (accounts, account_balances)
 * - 2026_03_25_000001_create_trial_balance_stored_procedures.php (sp_get_trial_balance)
 */
class TrialBalanceRepository
{
    /**
     * Mendapatkan saldo per akun untuk periode dan cabang tertentu.
     * Menggunakan tabel account_balances yang sudah teragregasi.
     */
    public function getBalancesByAccount(string $periodUuid, ?string $branchUuid = null): array
    {
        $period = ClosingPeriod::findOrFail($periodUuid);
        
        $query = AccountBalance::where('period', $period->period)
            ->with(['account' => function($q) {
                $q->select('uuid', 'id', 'code', 'name', 'type');
            }]);

        if ($branchUuid !== null) {
            $query->where('branch_uuid', $branchUuid);
        }

        $results = $query->get();

        // Jika branch_uuid null, kita perlu melakukan sum manual jika account_balances per-cabang
        if ($branchUuid === null) {
            $aggregated = [];
            foreach ($results as $item) {
                $accUuid = $item->account_uuid;
                if (!isset($aggregated[$accUuid])) {
                    $aggregated[$accUuid] = [
                        'account' => $item->account,
                        'opening' => 0,
                        'debit' => 0,
                        'credit' => 0,
                        'closing' => 0,
                    ];
                }
                $aggregated[$accUuid]['opening'] += (float)$item->opening_balance;
                $aggregated[$accUuid]['debit'] += (float)$item->debit;
                $aggregated[$accUuid]['credit'] += (float)$item->credit;
                $aggregated[$accUuid]['closing'] += (float)$item->closing_balance;
            }

            return array_map(function($data) {
                return new AccountBalanceEntity(
                    $data['account']->code,
                    $data['account']->name,
                    is_object($data['account']->type) ? $data['account']->type->value : (string)$data['account']->type,
                    $data['opening'],
                    $data['debit'],
                    $data['credit'],
                    $data['closing'],
                    $this->getNormalBalance($data['account']->type)
                );
            }, array_values($aggregated));
        }

        return $results->map(function($item) {
            return new AccountBalanceEntity(
                $item->account->code,
                $item->account->name,
                is_object($item->account->type) ? $item->account->type->value : (string)$item->account->type,
                (float)$item->opening_balance,
                (float)$item->debit,
                (float)$item->credit,
                (float)$item->closing_balance,
                $this->getNormalBalance($item->account->type)
            );
        })->toArray();
    }

    /**
     * Memanggil Stored Procedure sp_get_trial_balance untuk data volume besar.
     */
    public function getBalancesViaStoredProcedure(string $startDate, string $endDate, ?string $branchUuid = null): array
    {
        if (DB::getDriverName() === 'sqlite') {
            return $this->getBalancesViaQueryFallback($startDate, $endDate, $branchUuid);
        }

        // Parameter: p_branch_uuid, p_start_date, p_end_date, p_include_zero, p_account_type
        $results = DB::select('CALL sp_get_trial_balance(?, ?, ?, ?, ?)', [
            $branchUuid,
            $startDate,
            $endDate,
            true, // include zero
            null  // all types
        ]);

        return array_map(function($item) {
            return new AccountBalanceEntity(
                $item->account_code,
                $item->account_name,
                $item->account_type,
                (float)$item->opening_balance,
                (float)$item->total_debit,
                (float)$item->total_credit,
                (float)$item->closing_balance,
                $item->normal_balance
            );
        }, $results);
    }

    /**
     * helper untuk menentukan normal balance
     */
    private function getNormalBalance($type): string
    {
        $typeStr = is_object($type) ? $type->value : (string)$type;
        return in_array($typeStr, ['asset', 'expense']) ? 'debit' : 'credit';
    }

    private function getBalancesViaQueryFallback(string|int $startDate, string|int $endDate, ?string $branchUuid = null): array
    {
        $startTs = is_numeric($startDate) ? (int) $startDate : (strtotime((string) $startDate) ?: time());
        $endTs = is_numeric($endDate) ? (int) $endDate : (strtotime((string) $endDate) ?: time());

        $accounts = Account::query()
            ->where('is_header', 0)
            ->where(function ($query) {
                $query->where('deleted_at', 0)->orWhereNull('deleted_at');
            })
            ->orderBy('code')
            ->get(['uuid', 'code', 'name', 'type']);

        return $accounts->map(function (Account $account) use ($startTs, $endTs, $branchUuid) {
            $isDebitNormal = $this->getNormalBalance($account->type) === 'debit';
            $isProfitLoss = in_array((string) $account->type, ['revenue', 'expense', 'cogs', 'other_revenue', 'other_expense'], true);

            $openingPeriodBalance = 0.0;
            if (!$isProfitLoss) {
                $openingPeriodBalance = (float) (AccountBalance::query()
                    ->where('account_uuid', $account->uuid)
                    ->where('period', 'Opening')
                    ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                    ->where(function ($query) {
                        $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                    })
                    ->value('opening_balance') ?? 0);
            }

            $openingMutation = DB::table('general_ledgers')
                ->where('account_uuid', $account->uuid)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->where('journal_date', '<', $startTs)
                ->where(function ($query) {
                    $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
                ->first();

            $periodMutation = DB::table('general_ledgers')
                ->where('account_uuid', $account->uuid)
                ->when($branchUuid, fn ($query) => $query->where('branch_uuid', $branchUuid))
                ->whereBetween('journal_date', [$startTs, $endTs])
                ->where(function ($query) {
                    $query->where('deleted_at', 0)->orWhereNull('deleted_at');
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
                ->first();

            $openingNet = $isDebitNormal
                ? ((float) $openingMutation->debit - (float) $openingMutation->credit)
                : ((float) $openingMutation->credit - (float) $openingMutation->debit);

            $openingBalance = $openingPeriodBalance + $openingNet;
            $totalDebit = (float) $periodMutation->debit;
            $totalCredit = (float) $periodMutation->credit;
            $closingBalance = $openingBalance + ($isDebitNormal ? ($totalDebit - $totalCredit) : ($totalCredit - $totalDebit));

            return new AccountBalanceEntity(
                $account->code,
                $account->name,
                (string) $account->type,
                $openingBalance,
                $totalDebit,
                $totalCredit,
                $closingBalance,
                $this->getNormalBalance($account->type)
            );
        })->toArray();
    }
}
