<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockFilterEntity {
    public function __construct(public ?string $branch_uuid = null, public ?string $location_uuid = null, public ?string $product_uuid = null, public ?string $search = null, public int $page = 1, public int $per_page = 10) {}
}

