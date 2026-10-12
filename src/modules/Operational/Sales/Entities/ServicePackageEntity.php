<?php

namespace App\Modules\Operational\Sales\Entities;

class ServicePackageEntity
{
    /**
     * @param array<int,array> $items
     */
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public float|int|null $price = null,
        public ?bool $is_active = null,
        public array $items = [],
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: isset($data['code']) ? trim((string) $data['code']) : null,
            name: isset($data['name']) ? trim((string) $data['name']) : null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            items: array_values(array_filter($data['items'] ?? [], fn ($item) => is_array($item))),
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'items' => $this->items,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }
}