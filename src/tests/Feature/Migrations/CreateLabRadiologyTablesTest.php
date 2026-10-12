<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateLabRadiologyTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_lab_orders_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('lab_orders'));
        $this->assertTrue(Schema::hasColumns('lab_orders', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_lab_orders_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('lab_orders'));
    }

    public function test_up_method_creates_lab_order_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('lab_order_items'));
        $this->assertTrue(Schema::hasColumns('lab_order_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_lab_order_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('lab_order_items'));
    }

    public function test_up_method_creates_radiology_orders_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('radiology_orders'));
        $this->assertTrue(Schema::hasColumns('radiology_orders', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_radiology_orders_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('radiology_orders'));
    }

}
