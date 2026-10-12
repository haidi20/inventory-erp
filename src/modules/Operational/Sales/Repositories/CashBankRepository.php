<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use Illuminate\Support\Collection;

class CashBankRepository
{
    public function getCashAccounts(string $companyUuid): Collection
    {
        return Account::query()
            ->where('company_uuid', $companyUuid)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('code', 'like', '11%')
                    ->orWhere('name', 'like', '%Kas%')
                    ->orWhere('name', 'like', '%Bank%');
            })
            ->orderBy('code')
            ->get();
    }

    public function getLatestBalances(Collection $accounts, string $period, string $companyUuid): Collection
    {
        return $accounts->map(function ($account) use ($period, $companyUuid) {
            $balance = AccountBalance::query()
                ->where('company_uuid', $companyUuid)
                ->where('account_uuid', $account->uuid)
                ->where('period', '<=', $period)
                ->orderByDesc('period')
                ->first();

            return [
                'account' => $account,
                'period' => $balance?->period ?? $period,
                'opening_balance' => (float) ($balance?->opening_balance ?? 0),
                'total_debit' => (float) ($balance?->total_debit ?? 0),
                'total_credit' => (float) ($balance?->total_credit ?? 0),
                'closing_balance' => (float) ($balance?->closing_balance ?? 0),
            ];
        });
    }

    public function getTodayMutationSummary(array $accountUuids, string $companyUuid): object
    {
        if ($accountUuids === []) {
            return (object) ['total_debit' => 0, 'total_credit' => 0];
        }

        return GeneralLedger::query()
            ->where('company_uuid', $companyUuid)
            ->whereIn('account_uuid', $accountUuids)
            ->where('journal_date', date('Y-m-d'))
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();
    }

    public function getRecentMutations(array $accountUuids, string $companyUuid)
    {
        if ($accountUuids === []) {
            return collect();
        }

        return GeneralLedger::with(['account', 'journal'])
            ->where('company_uuid', $companyUuid)
            ->whereIn('account_uuid', $accountUuids)
            ->latest('journal_date')
            ->limit(15)
            ->get();
    }
}
