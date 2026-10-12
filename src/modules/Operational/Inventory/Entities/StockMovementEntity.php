<?php

namespace App\Modules\Operational\Inventory\Entities;

use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;

class StockMovementEntity {
    public function __construct(
        public ?string $uuid = null, 
        public string $branch_uuid = "", 
        public ?string $company_uuid = null, 
        public string $product_uuid = "", 
        public ?string $location_uuid = null, 
        public int $date = 0, 
        public string $type = "IN", 
        public ?StockMovementReferenceType $ref_type = null,
        public ?string $ref_uuid = null, 
        public ?string $unit_uuid = null, 
        public float $qty = 0, 
        public float $cost_price = 0, 
        public ?string $created_by = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'product_uuid' => $this->product_uuid,
            'location_uuid' => $this->location_uuid,
            'date' => $this->date,
            'type' => $this->type,
            'ref_type' => $this->ref_type?->value,
            'ref_uuid' => $this->ref_uuid,
            'unit_uuid' => $this->unit_uuid,
            'qty' => $this->qty,
            'cost_price' => $this->cost_price,
            'created_by' => $this->created_by,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            product_uuid: $model->product_uuid,
            location_uuid: $model->location_uuid,
            date: (int) $model->date,
            type: $model->type,
            ref_type: StockMovementReferenceType::fromValue($model->reference_type),
            ref_uuid: $model->reference_uuid,
            unit_uuid: $model->unit_uuid,
            qty: (float) $model->qty,
            cost_price: (float) $model->cost_price,
            created_by: $model->created_by,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? "",
            company_uuid: $data['company_uuid'] ?? null,
            product_uuid: $data['product_uuid'] ?? "",
            location_uuid: $data['location_uuid'] ?? null,
            date: (int) ($data['date'] ?? 0),
            type: $data['type'] ?? "IN",
            ref_type: StockMovementReferenceType::fromValue($data['ref_type'] ?? $data['reference_type'] ?? null),
            ref_uuid: $data['ref_uuid'] ?? $data['reference_uuid'] ?? null,
            unit_uuid: $data['unit_uuid'] ?? null,
            qty: (float) ($data['qty'] ?? 0),
            cost_price: (float) ($data['cost_price'] ?? $data['price'] ?? 0),
            created_by: $data['created_by'] ?? $data['user_uuid'] ?? null,
        );
    }
}
