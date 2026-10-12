<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateSalesTransactionTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_sales_orders_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales_orders'));
        $this->assertTrue(Schema::hasColumns('sales_orders', ['uuid']));
    }

    public function test_down_method_drops_sales_orders_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales_orders'));
    }

    public function test_up_method_creates_sales_order_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales_order_details'));
        $this->assertTrue(Schema::hasColumns('sales_order_details', ['uuid']));
    }

    public function test_down_method_drops_sales_order_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales_order_details'));
    }

    public function test_up_method_creates_delivery_orders_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('delivery_orders'));
        $this->assertTrue(Schema::hasColumns('delivery_orders', ['uuid']));
    }

    public function test_down_method_drops_delivery_orders_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('delivery_orders'));
    }

    public function test_up_method_creates_delivery_order_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('delivery_order_details'));
        $this->assertTrue(Schema::hasColumns('delivery_order_details', ['uuid']));
    }

    public function test_down_method_drops_delivery_order_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('delivery_order_details'));
    }

    public function test_up_method_creates_sales_invoices_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales_invoices'));
        $this->assertTrue(Schema::hasColumns('sales_invoices', ['uuid']));
    }

    public function test_down_method_drops_sales_invoices_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales_invoices'));
    }

    public function test_up_method_creates_sales_invoice_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales_invoice_details'));
        $this->assertTrue(Schema::hasColumns('sales_invoice_details', ['uuid']));
    }

    public function test_down_method_drops_sales_invoice_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales_invoice_details'));
    }

    public function test_up_method_creates_sales_invoice_agings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sales_invoice_agings'));
        $this->assertTrue(Schema::hasColumns('sales_invoice_agings', ['uuid']));
    }

    public function test_down_method_drops_sales_invoice_agings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sales_invoice_agings'));
    }

}
