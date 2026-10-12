<?php
namespace App\Modules\Operational\Inventory\Entities;
class StockTransferItemEntity {
    public function __construct(public ?string $uuid = null, public ?string $transfer_uuid = null, public string $product_uuid = "", public string $unit_uuid = "", public float $qty = 0) {}
}

