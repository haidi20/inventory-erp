<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockAdjustmentListResultEntity {
    public function __construct(public array $data, public int $total, public int $per_page, public int $current_page) {}
}

