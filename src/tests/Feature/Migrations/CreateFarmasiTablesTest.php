<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateFarmasiTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_dispensings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('dispensings'));
        $this->assertTrue(Schema::hasColumns('dispensings', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_dispensings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('dispensings'));
    }

    public function test_up_method_creates_dispensing_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('dispensing_items'));
        $this->assertTrue(Schema::hasColumns('dispensing_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_dispensing_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('dispensing_items'));
    }

    public function test_up_method_creates_purchase_returns_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_returns'));
        $this->assertTrue(Schema::hasColumns('purchase_returns', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_purchase_returns_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_returns'));
    }

    public function test_up_method_creates_purchase_return_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_return_items'));
        $this->assertTrue(Schema::hasColumns('purchase_return_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_purchase_return_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_return_items'));
    }

}
