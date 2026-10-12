<?php

namespace App\Modules\Operational\Procurement\Entities;

class PurchaseReturnItemEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $item_uuid = null,
        public ?string $unit_uuid = null,
        public ?string $barcode = null,
        public float $quantity = 0,
        public float $input_price = 0,
        public float $price = 0,
        public float $discount = 0,
        public float $subtotal = 0,
        public ?string $notes = null
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            item_uuid: $model->item_uuid,
            unit_uuid: $model->unit_uuid,
            quantity: (float) ($model->qty ?? $model->quantity ?? 0),
            price: (float) ($model->price ?? 0),
            subtotal: (float) ($model->total ?? $model->subtotal ?? 0),
            notes: $model->notes ?? null,
        );
    }
}

