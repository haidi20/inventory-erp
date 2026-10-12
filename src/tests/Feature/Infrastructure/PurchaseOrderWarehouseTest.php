<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseOrderWarehouseTest extends TestCase
{
    public function test_purchase_orders_has_no_warehouse_uuid_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('purchase_orders', 'warehouse_uuid'),
            'warehouse_uuid harus sudah dihapus dari purchase_orders header (multi-warehouse per GR)'
        );
    }

    public function test_purchase_orders_has_location_uuid_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('purchase_orders', 'location_uuid'),
            'purchase_orders harus pakai location_uuid (bukan warehouse_uuid)'
        );
    }

    public function test_goods_receipts_has_location_uuid_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('goods_receipts', 'location_uuid'),
            'goods_receipts harus punya location_uuid untuk multi-warehouse penerimaan'
        );
    }

    public function test_purchase_order_items_has_no_warehouse_uuid_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('purchase_order_items', 'warehouse_uuid'),
            'purchase_order_items tidak perlu warehouse_uuid (lokasi ada di GR)'
        );
    }
}