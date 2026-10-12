<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * ProductPriceEntity
 * 
 * Entitas untuk mengelola data harga produk (Price List).
 */
class ProductPriceEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $item_uuid = null,
        public ?string $price_level_uuid = null,
        public float $price = 0.0,
        public ?int $start_date = null,
        public bool $is_active = true,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'item_uuid' => $this->item_uuid,
            'price_level_uuid' => $this->price_level_uuid,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            item_uuid: $model->item_uuid,
            price_level_uuid: $model->price_level_uuid,
            price: (float) $model->price,
            start_date: (int) ($model->start_date ?? $model->started_at ?? 0),
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            item_uuid: $data['item_uuid'] ?? null,
            price_level_uuid: $data['price_level_uuid'] ?? null,
            price: (float) ($data['price'] ?? 0.0),
            start_date: isset($data['start_date']) ? (int) $data['start_date'] : (isset($data['started_at']) ? (int) $data['started_at'] : time()),
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
