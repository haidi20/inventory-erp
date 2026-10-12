<?php

namespace App\Modules\Finance\Ledger\Entities;

class AccountBalanceEntity
{
    public function __construct(
        public string $account_code,
        public string $account_name,
        public string $account_type,
        public float $opening_balance,
        public float $total_debit,
        public float $total_credit,
        public float $closing_balance,
        public string $normal_balance
    ) {}

    public function toArray(): array
    {
        return [
            'account_code' => $this->account_code,
            'account_name' => $this->account_name,
            'account_type' => $this->account_type,
            'opening_balance' => $this->opening_balance,
            'total_debit' => $this->total_debit,
            'total_credit' => $this->total_credit,
            'closing_balance' => $this->closing_balance,
            'normal_balance' => $this->normal_balance,
        ];
    }
}
