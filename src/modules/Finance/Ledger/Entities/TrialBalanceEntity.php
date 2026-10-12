<?php

namespace App\Modules\Finance\Ledger\Entities;

use App\Modules\Finance\Journal\Entities\PeriodInfoEntity;
use App\Modules\Finance\Journal\Entities\TotalsEntity;

class TrialBalanceEntity
{
    /**
     * @param AccountBalanceEntity[] $accounts
     */
    public function __construct(
        public PeriodInfoEntity $period_info,
        public ?string $branch_uuid,
        public array $accounts,
        public TotalsEntity $totals,
        public bool $is_balanced,
        public string $generated_at
    ) {}

    public function toArray(): array
    {
        return [
            'period_info' => $this->period_info->toArray(),
            'branch_uuid' => $this->branch_uuid,
            'accounts' => array_map(fn($account) => $account->toArray(), $this->accounts),
            'totals' => $this->totals->toArray(),
            'is_balanced' => $this->is_balanced,
            'generated_at' => $this->generated_at,
        ];
    }
}
