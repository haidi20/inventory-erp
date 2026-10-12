<?php

namespace App\Modules\Operational\Procurement\Entities;

/**
 * PurchaseItemEntity
 * 
 * Entitas untuk mengelola item dalam Transaksi Pembelian.
 */
class PurchaseItemEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $product_uuid = null,
        public float $qty = 0.0,
        public float $price = 0.0,
        public float $subtotal = 0.0,
        public ?string $unit_uuid = null
    ) {}
}

