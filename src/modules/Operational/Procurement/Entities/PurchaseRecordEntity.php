<?php

namespace App\Modules\Operational\Procurement\Entities;

use App\Modules\Operational\Sales\Enums\TransactionStatus;

/**
 * PurchaseRecordEntity
 * 
 * Entitas untuk mengelola dokumen Transaksi Pembelian (Purchase Invoice/Record).
 */
class PurchaseRecordEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $supplier_uuid = null,
        public ?string $purchase_order_uuid = null,
        public ?string $location_uuid = null,
        public ?string $document_no = null,
        public ?string $reference_no = null,
        public ?string $date = null,
        public ?string $description = null,
        public TransactionStatus $status = TransactionStatus::POSTED,
        public float $total_amount = 0.0,
        public array $items = [],
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'supplier_uuid' => $this->supplier_uuid,
            'purchase_order_uuid' => $this->purchase_order_uuid,
            'document_no' => $this->document_no,
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'description' => $this->description,
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            supplier_uuid: $model->supplier_uuid,
            purchase_order_uuid: $model->purchase_order_uuid,
            document_no: $model->invoice_no,
            reference_no: $model->reference_no,
            date: (string) $model->purchase_date,
            description: $model->description,
            status: $model->status instanceof TransactionStatus ? $model->status : (TransactionStatus::tryFrom($model->status) ?? TransactionStatus::POSTED),
            total_amount: (float) $model->total,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            supplier_uuid: $data['supplier_uuid'] ?? null,
            purchase_order_uuid: $data['purchase_order_uuid'] ?? null,
            location_uuid: $data['location_uuid'] ?? null,
            document_no: $data['document_no'] ?? $data['invoice_no'] ?? $data['purchase_no'] ?? null,
            reference_no: $data['reference_no'] ?? null,
            date: $data['date'] ?? $data['purchase_date'] ?? null,
            description: $data['description'] ?? null,
            status: ($data['status'] ?? null) instanceof TransactionStatus ? $data['status'] : (is_string($data['status'] ?? null) ? (TransactionStatus::tryFrom($data['status']) ?? TransactionStatus::POSTED) : TransactionStatus::POSTED),
            total_amount: (float) ($data['total_amount'] ?? $data['total'] ?? 0.0),
            items: $data['items'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}

