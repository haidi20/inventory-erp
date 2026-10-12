<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockTransferListResultEntity {
    public function __construct(public array $items, public int $total, public int $per_page, public int $current_page) {}
}

