<?php

namespace App\Modules\Operational\Procurement\Entities;

class PurchaseOrderListResultEntity
{
    public function __construct(
        public array $items,
        public int $total,
        public int $page
    ) {}
}

