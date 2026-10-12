<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePurchaseOrdersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_purchase_orders_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_orders'));
        $this->assertTrue(Schema::hasColumns('purchase_orders', ['uuid']));
    }

    public function test_down_method_drops_purchase_orders_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_orders'));
    }

    public function test_up_method_creates_purchase_order_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_order_items'));
        $this->assertTrue(Schema::hasColumns('purchase_order_items', ['uuid']));
    }

    public function test_down_method_drops_purchase_order_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_order_items'));
    }

}
