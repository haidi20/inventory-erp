<?php

namespace App\Modules\Operational\Manufacturing\Entities;

/**
 * BomEntity
 * 
 * Entitas untuk mengelola data Bill of Materials (BOM).
 */
class BomEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $item_uuid = null,
        public ?string $unit_uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public float $qty = 0.0,
        public ?string $description = null,
        public bool $is_active = true,
        public array $items = [],
        public ?string $actor_uuid = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'item_uuid' => $this->item_uuid,
            'unit_uuid' => $this->unit_uuid,
            'code' => $this->code,
            'name' => $this->name,
            'qty' => $this->qty,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid:         $model->uuid,
            branch_uuid:  $model->branch_uuid,
            company_uuid: $model->company_uuid,
            item_uuid:    $model->item_uuid,
            unit_uuid:    $model->unit_uuid,
            code:         $model->document_no,
            name:         $model->document_no,
            qty:          (float) ($model->quantity ?? 0),
            description:  $model->note,
            is_active:    (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            item_uuid: $data['item_uuid'] ?? null,
            unit_uuid: $data['unit_uuid'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            qty: (float) ($data['qty'] ?? 0.0),
            description: $data['description'] ?? null,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            items: $data['items'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            search: $data['search'] ?? null,
        );
    }
}

