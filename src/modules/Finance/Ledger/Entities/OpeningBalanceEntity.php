<?php

namespace App\Modules\Finance\Ledger\Entities;

/**
 * OpeningBalanceEntity
 * 
 * Entitas untuk mengelola data saldo awal stok barang (Item) maupun hutang/piutang (Person).
 */
class OpeningBalanceEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $product_uuid = null,
        public ?string $location_uuid = null,
        public ?string $person_uuid = null,
        public ?string $unit_uuid = null,
        public ?string $transaction_no = null,
        public ?int $transaction_date = null,
        public ?int $period_month = null,
        public ?string $type = null,
        public ?string $note = null,
        public bool $is_posted = false,
        public float $qty = 0.0,
        public float $amount = 0.0,
        public ?int $date = null,
        public ?string $period_name = null,
        public ?string $actor_uuid = null
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            product_uuid: $model->product_uuid ?? $model->item_uuid ?? null,
            location_uuid: $model->location_uuid,
            person_uuid: $model->person_uuid,
            unit_uuid: $model->unit_uuid,
            transaction_no: $model->transaction_no ?? null,
            transaction_date: isset($model->transaction_date) ? (int) $model->transaction_date : null,
            period_month: isset($model->period_month) ? (int) $model->period_month : null,
            type: $model->type ?? null,
            note: $model->note ?? null,
            is_posted: (bool) ($model->is_posted ?? false),
            qty: (float) ($model->qty ?? 0.0),
            amount: (float) ($model->amount ?? $model->opening_balance ?? 0.0),
            date: isset($model->date) ? (int) $model->date : null,
            period_name: $model->period_name,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            product_uuid: $data['product_uuid'] ?? $data['item_uuid'] ?? null,
            location_uuid: $data['location_uuid'] ?? null,
            person_uuid: $data['person_uuid'] ?? null,
            unit_uuid: $data['unit_uuid'] ?? null,
            transaction_no: $data['transaction_no'] ?? null,
            transaction_date: isset($data['transaction_date']) ? (int) $data['transaction_date'] : null,
            period_month: isset($data['period_month']) ? (int) $data['period_month'] : null,
            type: $data['type'] ?? null,
            note: $data['note'] ?? null,
            is_posted: (bool) ($data['is_posted'] ?? false),
            qty: (float) ($data['qty'] ?? 0.0),
            amount: (float) ($data['amount'] ?? 0.0),
            date: isset($data['date']) ? (int) $data['date'] : null,
            period_name: $data['period_name'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'product_uuid' => $this->product_uuid,
            'location_uuid' => $this->location_uuid,
            'person_uuid' => $this->person_uuid,
            'unit_uuid' => $this->unit_uuid,
            'transaction_no' => $this->transaction_no,
            'transaction_date' => $this->transaction_date,
            'period_month' => $this->period_month,
            'type' => $this->type,
            'note' => $this->note,
            'is_posted' => $this->is_posted,
            'qty' => $this->qty,
            'amount' => $this->amount,
            'date' => $this->date,
            'period_name' => $this->period_name,
        ];
    }
}
