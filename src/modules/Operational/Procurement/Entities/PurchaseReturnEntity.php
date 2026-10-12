<?php

namespace App\Modules\Operational\Procurement\Entities;

/**
 * PurchaseReturnEntity
 * 
 * Entitas untuk mengelola dokumen Retur Pembelian.
 */
class PurchaseReturnEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $supplier_uuid = null,
        public ?string $location_uuid = null,
        public ?string $purchase_invoice_uuid = null,
        public ?string $term_uuid = null,
        public ?string $document_no = null,
        public ?string $reference_no = null,
        public ?int $date = null,
        public string $status = 'posted',
        public ?string $notes = null,
        public float $tax_amount = 0.0,
        public float $total = 0.0,
        public float $net_amount = 0.0,
        public array $items = [],
        public array $payments = [],
        public ?string $actor_uuid = null
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            supplier_uuid: $model->supplier_uuid,
            location_uuid: $model->location_uuid,
            term_uuid: $model->payment_term_uuid,
            document_no: $model->document_no,
            reference_no: $model->reference_no,
            date: (int) $model->date,
            status: $model->status ?? 'posted',
            notes: $model->notes,
            total: (float) ($model->total_amount ?? 0),
            net_amount: (float) ($model->net_amount ?? $model->total_amount ?? 0),
            items: $model->relationLoaded('items')
                ? $model->items->map(fn ($item) => PurchaseReturnItemEntity::fromModel($item))->all()
                : [],
            payments: $model->relationLoaded('payments') ? $model->payments->all() : [],
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'supplier_uuid' => $this->supplier_uuid,
            'location_uuid' => $this->location_uuid,
            'payment_term_uuid' => $this->term_uuid,
            'document_no' => $this->document_no,
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'status' => $this->status,
            'notes' => $this->notes,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total,
            'net_amount' => $this->net_amount,
            'items' => $this->items,
            'payments' => $this->payments,
            'actor_uuid' => $this->actor_uuid,
        ];
    }
}

