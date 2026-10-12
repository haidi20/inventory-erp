<?php

namespace App\Modules\Analytics\Reports\Repositories;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;

class FinancialStatementMenuRepository
{
    public function getActivePeriod(): ?ClosingPeriod
    {
        return ClosingPeriod::query()
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('end_date')
            ->first();
    }

    public function getAccountCount(): int
    {
        return Account::query()
            ->where('is_active', true)
            ->count();
    }

    public function getJournalSummary(?ClosingPeriod $period): array
    {
        $query = Journal::query();

        if ($period) {
            $query->whereBetween('journal_date', [$period->start_date, $period->end_date]);
        }

        return [
            'total' => (clone $query)->count(),
            'posted' => (clone $query)->where('status', 'posted')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'last_date' => (clone $query)->max('journal_date'),
        ];
    }

    public function getBalanceCoverage(?ClosingPeriod $period): int
    {
        if (!$period) {
            return 0;
        }

        return AccountBalance::query()
            ->where('period', $period->period)
            ->count();
    }
}

