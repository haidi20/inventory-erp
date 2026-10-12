<?php

namespace App\Modules\Operational\Procurement\Entities;

class PosPurchaseEntity
{
    /**
     * @param array<int,array> $items
     */
    public function __construct(
        public ?string $invoice_no = null,
        public ?string $supplier_uuid = null,
        public ?string $purchase_date = null,
        public float|int|null $paid = null,
        public ?string $status = null,
        public array $items = [],
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public float|int|null $total = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            invoice_no: isset($data['invoice_no']) ? trim((string) $data['invoice_no']) : null,
            supplier_uuid: $data['supplier_uuid'] ?? null,
            purchase_date: $data['purchase_date'] ?? null,
            paid: isset($data['paid']) && $data['paid'] !== '' ? (float) $data['paid'] : null,
            status: isset($data['status']) ? trim((string) $data['status']) : null,
            items: array_values(array_filter($data['items'] ?? [], fn ($item) => is_array($item))),
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            total: isset($data['total']) ? (float) $data['total'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'invoice_no' => $this->invoice_no,
            'supplier_uuid' => $this->supplier_uuid,
            'purchase_date' => $this->purchase_date,
            'paid' => $this->paid,
            'status' => $this->status,
            'items' => $this->items,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'total' => $this->total,
        ];
    }
}