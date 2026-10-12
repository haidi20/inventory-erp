<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Repositories\CashBankRepository;

class CashBankService
{
    public function __construct(private CashBankRepository $cashBankRepository)
    {
    }

    public function index(?string $period = null, string $companyUuid = null): array
    {
        $companyUuid = $companyUuid ?? current_company_uuid();
        $resolvedPeriod = preg_match('/^\d{4}-\d{2}$/', (string) $period) ? $period : now()->format('Y-m');

        $accounts = $this->cashBankRepository->getCashAccounts($companyUuid);
        $balances = $this->cashBankRepository->getLatestBalances($accounts, $resolvedPeriod, $companyUuid);
        $accountUuids = $accounts->pluck('uuid')->all();
        $todayMutationSummary = $this->cashBankRepository->getTodayMutationSummary($accountUuids, $companyUuid);

        return [
            'period' => $resolvedPeriod,
            'balances' => $balances,
            'recentMutations' => $this->cashBankRepository->getRecentMutations($accountUuids, $companyUuid),
            'stats' => [
                'account_count' => $accounts->count(),
                'closing_balance' => $balances->sum('closing_balance'),
                'cash_in_today' => (float) ($todayMutationSummary->total_debit ?? 0),
                'cash_out_today' => (float) ($todayMutationSummary->total_credit ?? 0),
            ],
        ];
    }
}
