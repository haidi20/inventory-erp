<?php

namespace App\Modules\Operational\Inventory\Entities;

/**
 * StockEntity
 * 
 * Entitas untuk mengelola pergerakan stok, saldo, dan status inventaris.
 */
class StockEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $product_uuid = null,
        public ?string $location_uuid = null,
        public ?string $type = null, // IN / OUT
        public float $qty = 0.0,
        public float $cost_price = 0.0,
        public ?string $ref_type = null,
        public ?string $ref_uuid = null,
        public ?int $date = null,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?string $actor_uuid = null
    ) {}
}

