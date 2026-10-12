<?php

namespace App\Modules\Operational\Sales\Entities;

class SaleEntity
{
    /**
     * @param array<int,array> $items
     */
    public function __construct(
        public ?string $sale_no = null,
        public ?string $patient_uuid = null,
        public ?string $cashier_uuid = null,
        public float|int|null $paid = null,
        public ?string $payment_status = null,
        public ?string $payment_method = null,
        public array $items = [],
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public float|int|null $total_amount = null,
        public float|int|null $change = null,
        public int|string|null $sale_at = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sale_no: isset($data['sale_no']) ? trim((string) $data['sale_no']) : null,
            patient_uuid: $data['patient_uuid'] ?? null,
            cashier_uuid: $data['cashier_uuid'] ?? null,
            paid: isset($data['paid']) && $data['paid'] !== '' ? (float) $data['paid'] : null,
            payment_status: isset($data['payment_status']) ? trim((string) $data['payment_status']) : null,
            payment_method: isset($data['payment_method']) ? trim((string) $data['payment_method']) : null,
            items: array_values(array_filter($data['items'] ?? [], fn ($item) => is_array($item))),
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            total_amount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            change: isset($data['change']) ? (float) $data['change'] : null,
            sale_at: $data['sale_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'sale_no' => $this->sale_no,
            'patient_uuid' => $this->patient_uuid,
            'cashier_uuid' => $this->cashier_uuid,
            'paid' => $this->paid,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'items' => $this->items,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'total_amount' => $this->total_amount,
            'change' => $this->change,
            'sale_at' => $this->sale_at,
        ];
    }
}