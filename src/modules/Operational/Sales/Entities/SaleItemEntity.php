<?php

namespace App\Modules\Operational\Sales\Entities;

class SaleItemEntity
{
    public function __construct(
        public ?string $product_uuid = null,
        public ?int $qty = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            product_uuid: $data['product_uuid'] ?? null,
            qty: isset($data['qty']) ? (int) $data['qty'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'product_uuid' => $this->product_uuid,
            'qty' => $this->qty,
        ];
    }
}

