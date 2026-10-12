<?php

namespace App\Modules\Operational\Procurement\Entities;

class PurchaseOrderItemEntity
{
    public function __construct(
        public ?string $uuid = null,
        public string $product_uuid = '',
        public string $unit_uuid = '',
        public float $quantity = 0,
        public float $price = 0,
        public float $discount = 0,
        public float $subtotal = 0,
        public ?string $notes = null
    ) {}
}

