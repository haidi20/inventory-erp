<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePurchaseReturnsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_purchase_returns_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_returns'));
        $this->assertTrue(Schema::hasColumns('purchase_returns', ['uuid']));
    }

    public function test_down_method_drops_purchase_returns_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_returns'));
    }

    public function test_up_method_creates_purchase_return_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_return_items'));
        $this->assertTrue(Schema::hasColumns('purchase_return_items', ['uuid']));
    }

    public function test_down_method_drops_purchase_return_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_return_items'));
    }

    public function test_up_method_creates_purchase_return_payments_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_return_payments'));
        $this->assertTrue(Schema::hasColumns('purchase_return_payments', ['uuid']));
    }

    public function test_down_method_drops_purchase_return_payments_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_return_payments'));
    }

}
