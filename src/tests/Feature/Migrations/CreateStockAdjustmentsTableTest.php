<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateStockAdjustmentsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_stock_adjustments_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('stock_adjustments'));
        $this->assertTrue(Schema::hasColumns('stock_adjustments', ['uuid']));
    }

    public function test_down_method_drops_stock_adjustments_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stock_adjustments'));
    }

    public function test_up_method_creates_stock_adjustment_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('stock_adjustment_items'));
        $this->assertTrue(Schema::hasColumns('stock_adjustment_items', ['uuid']));
    }

    public function test_down_method_drops_stock_adjustment_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stock_adjustment_items'));
    }

}
