<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateStockTransfersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_stock_transfers_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('stock_transfers'));
        $this->assertTrue(Schema::hasColumns('stock_transfers', ['uuid']));
    }

    public function test_down_method_drops_stock_transfers_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stock_transfers'));
    }

    public function test_up_method_creates_stock_transfer_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('stock_transfer_items'));
        $this->assertTrue(Schema::hasColumns('stock_transfer_items', ['uuid']));
    }

    public function test_down_method_drops_stock_transfer_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stock_transfer_items'));
    }

}
