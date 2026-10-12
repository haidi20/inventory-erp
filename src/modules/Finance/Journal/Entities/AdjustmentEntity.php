<?php

namespace App\Modules\Finance\Journal\Entities;

/**
 * AdjustmentEntity
 * 
 * Entitas untuk data jurnal penyesuaian (adjusting journal).
 */
class AdjustmentEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $period_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $adj_type = null,
        public ?string $description = null,
        public float $amount = 0.0,
        public ?string $debit_account_uuid = null,
        public ?string $credit_account_uuid = null,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            period_uuid: $data['period_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            adj_type: $data['adj_type'] ?? $data['type'] ?? null,
            description: $data['description'] ?? null,
            amount: (float) ($data['amount'] ?? 0.0),
            debit_account_uuid: $data['debit_account_uuid'] ?? null,
            credit_account_uuid: $data['credit_account_uuid'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'period_uuid' => $this->period_uuid,
            'branch_uuid' => $this->branch_uuid,
            'adj_type' => $this->adj_type,
            'description' => $this->description,
            'amount' => $this->amount,
            'debit_account_uuid' => $this->debit_account_uuid,
            'credit_account_uuid' => $this->credit_account_uuid,
            'actor_uuid' => $this->actor_uuid,
        ];
    }
}
