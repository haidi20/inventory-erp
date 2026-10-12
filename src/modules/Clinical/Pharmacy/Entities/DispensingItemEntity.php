<?php

namespace App\Modules\Clinical\Pharmacy\Entities;

class DispensingItemEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $dispensing_uuid = null,
        public ?string $product_uuid = null,
        public float|int|null $qty = null,
        public float|int|null $price_sell = null,
        public float|int|null $subtotal = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            dispensing_uuid: $data['dispensing_uuid'] ?? null,
            product_uuid: $data['product_uuid'] ?? null,
            qty: isset($data['qty']) ? (float) $data['qty'] : null,
            price_sell: isset($data['price_sell']) ? (float) $data['price_sell'] : null,
            subtotal: isset($data['subtotal']) ? (float) $data['subtotal'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product_uuid'    => $this->product_uuid,
            'qty'             => $this->qty,
            'price_sell'      => $this->price_sell,
            'subtotal'        => $this->subtotal ?? ($this->qty * $this->price_sell),
        ], fn($v) => $v !== null);
    }
}

