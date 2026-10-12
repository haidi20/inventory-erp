<?php

namespace App\Modules\Operational\Procurement\Entities;

/**
 * PurchaseListResultEntity
 * 
 * Entitas untuk membungkus hasil pencarian daftar pembelian dengan paginasi.
 */
class PurchaseListResultEntity
{
    /**
     * @param \App\Modules\Operational\Procurement\Models\Purchase[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $per_page,
        public int $current_page
    ) {}
}

