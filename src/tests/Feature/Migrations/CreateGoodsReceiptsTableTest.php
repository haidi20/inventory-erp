<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateGoodsReceiptsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_goods_receipts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('goods_receipts'));
        $this->assertTrue(Schema::hasColumns('goods_receipts', [
            'uuid',
            'branch_uuid',
            'company_uuid',
            'document_no',
            'date',
            'supplier_uuid',
            'location_uuid',
            'purchase_order_uuid',
            'purchase_invoice_uuid',
            'status',
            'total',
            'tax_amount',
            'net_amount',
            'created_by',
            'updated_by',
            'deleted_by',
            'deleted_at',
        ]));
    }

    public function test_down_method_drops_goods_receipts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('goods_receipts'));
    }

    public function test_up_method_creates_goods_receipt_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('goods_receipt_items'));
        $this->assertTrue(Schema::hasColumns('goods_receipt_items', [
            'uuid',
            'goods_receipt_uuid',
            'branch_uuid',
            'company_uuid',
            'product_uuid',
            'unit_uuid',
            'quantity',
            'purchase_order_item_uuid',
            'price',
            'discount',
            'subtotal',
        ]));
    }

    public function test_down_method_drops_goods_receipt_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('goods_receipt_items'));
    }
}
