<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockAdjustmentItemEntity {
    public function __construct(public ?string $uuid = null, public string $product_uuid = "", public string $measurement_uuid = "", public float $adjustment_qty = 0, public float $price = 0, public float $book_qty = 0, public float $actual_qty = 0, public ?string $reason = null, public ?string $notes = null) {}
}

