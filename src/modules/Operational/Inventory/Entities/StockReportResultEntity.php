<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockReportResultEntity {
    public function __construct(public array $items, public int $total, public int $per_page, public int $current_page) {}
}

