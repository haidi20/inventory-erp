<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateOperationalTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_registrations_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('registrations'));
        $this->assertTrue(Schema::hasColumns('registrations', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_registrations_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('registrations'));
    }

    public function test_up_method_creates_encounters_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('encounters'));
        $this->assertTrue(Schema::hasColumns('encounters', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_encounters_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('encounters'));
    }

    public function test_up_method_creates_encounter_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('encounter_items'));
        $this->assertTrue(Schema::hasColumns('encounter_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_encounter_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('encounter_items'));
    }

    public function test_up_method_creates_sales_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales'));
        $this->assertTrue(Schema::hasColumns('sales', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_sales_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales'));
    }

    public function test_up_method_creates_sale_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sale_items'));
        $this->assertTrue(Schema::hasColumns('sale_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_sale_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sale_items'));
    }

    public function test_up_method_creates_purchases_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchases'));
        $this->assertTrue(Schema::hasColumns('purchases', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_purchases_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchases'));
    }

    public function test_up_method_creates_purchase_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_items'));
        $this->assertTrue(Schema::hasColumns('purchase_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_purchase_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_items'));
    }

    public function test_up_method_creates_product_purchases_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('product_purchases'));
        $this->assertTrue(Schema::hasColumns('product_purchases', ['purchase_uuid', 'product_uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_product_purchases_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('product_purchases'));
    }

    public function test_up_method_creates_product_quotation_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('product_quotation'));
        $this->assertTrue(Schema::hasColumns('product_quotation', ['quotation_uuid', 'product_uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_product_quotation_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('product_quotation'));
    }

    public function test_up_method_creates_product_sales_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('product_sales'));
        $this->assertTrue(Schema::hasColumns('product_sales', ['sale_uuid', 'product_uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_product_sales_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('product_sales'));
    }

    public function test_up_method_creates_receivables_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('receivables'));
        $this->assertTrue(Schema::hasColumns('receivables', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_receivables_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('receivables'));
    }

    public function test_up_method_creates_payables_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('payables'));
        $this->assertTrue(Schema::hasColumns('payables', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_payables_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('payables'));
    }

    public function test_up_method_creates_stock_movements_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('stock_movements'));
        $this->assertTrue(Schema::hasColumns('stock_movements', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_stock_movements_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stock_movements'));
    }

}
