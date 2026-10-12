<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * TreatmentPackageEntity
 * 
 * Entitas untuk mengelola data paket layanan medis (Treatment/Service Package).
 */
class TreatmentPackageEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public float|int|null $price = 0.0,
        public bool $is_active = true,
        /** @var TreatmentPackageItemEntity[] */
        public array $items = [],
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'code' => $this->code,
            'name' => $this->name,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        $items = [];
        if (isset($model->items) && $model->relationLoaded('items')) {
            $items = $model->items->map(fn($m) => TreatmentPackageItemEntity::fromModel($m))->toArray();
        }

        return new self(
            uuid: $model->uuid,
            id: $model->id,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            code: $model->code,
            name: $model->name,
            price: (float) $model->price,
            is_active: (bool) $model->is_active,
            items: $items
        );
    }

    public static function fromArray(array $data): self
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = TreatmentPackageItemEntity::fromArray($item);
            }
        }

        return new self(
            uuid: $data['uuid'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            search: isset($data['search']) ? trim((string) $data['search']) : null,
            search_column: $data['search_column'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : 0.0,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            items: $items,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
