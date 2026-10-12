<?php

namespace App\Modules\Finance\Ledger\Entities;

/**
 * GeneralLedgerFilterEntity
 * 
 * Entitas untuk filter parameter laporan Buku Besar (General Ledger).
 */
class GeneralLedgerFilterEntity
{
    public function __construct(
        public ?string $account_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public string|int|null $date_from = null,
        public string|int|null $date_to = null,
        public ?string $keyword = null,
        public float $min_amount = 0.0,
        public float $max_amount = 0.0,
        public ?bool $is_reconciled = null,
        public ?string $reference_type = null,
        public ?string $period_uuid = null,
        public int $page = 1,
        public int $per_page = 50,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            account_uuid: $data['account_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            date_from: $data['date_from'] ?? $data['start_date'] ?? null,
            date_to: $data['date_to'] ?? $data['end_date'] ?? null,
            keyword: $data['keyword'] ?? $data['search'] ?? null,
            min_amount: (float) ($data['min_amount'] ?? 0.0),
            max_amount: (float) ($data['max_amount'] ?? 0.0),
            is_reconciled: isset($data['is_reconciled']) ? filter_var($data['is_reconciled'], FILTER_VALIDATE_BOOLEAN) : null,
            reference_type: $data['reference_type'] ?? null,
            period_uuid: $data['period_uuid'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 50),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
