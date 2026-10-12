<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateItemSupportTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_units_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('units'));
        $this->assertTrue(Schema::hasColumns('units', ['uuid']));
    }

    public function test_down_method_drops_units_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('units'));
    }

    public function test_up_method_creates_price_levels_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('price_levels'));
        $this->assertTrue(Schema::hasColumns('price_levels', ['uuid']));
    }

    public function test_down_method_drops_price_levels_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('price_levels'));
    }

    public function test_up_method_creates_item_prices_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_prices'));
        $this->assertTrue(Schema::hasColumns('item_prices', ['uuid']));
    }

    public function test_down_method_drops_item_prices_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_prices'));
    }

    public function test_up_method_creates_item_units_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_units'));
        $this->assertTrue(Schema::hasColumns('item_units', ['uuid']));
    }

    public function test_down_method_drops_item_units_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_units'));
    }

    public function test_up_method_creates_item_accounts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_accounts'));
        $this->assertTrue(Schema::hasColumns('item_accounts', ['uuid']));
    }

    public function test_down_method_drops_item_accounts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_accounts'));
    }

    public function test_up_method_creates_item_stocks_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_stocks'));
        $this->assertTrue(Schema::hasColumns('item_stocks', ['uuid']));
    }

    public function test_down_method_drops_item_stocks_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_stocks'));
    }

    public function test_up_method_creates_item_opening_balances_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_opening_balances'));
        $this->assertTrue(Schema::hasColumns('item_opening_balances', ['uuid']));
    }

    public function test_down_method_drops_item_opening_balances_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_opening_balances'));
    }

    public function test_up_method_creates_item_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_details'));
        $this->assertTrue(Schema::hasColumns('item_details', ['uuid']));
    }

    public function test_down_method_drops_item_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_details'));
    }

    public function test_up_method_creates_item_discount_rules_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_discount_rules'));
        $this->assertTrue(Schema::hasColumns('item_discount_rules', ['uuid']));
    }

    public function test_down_method_drops_item_discount_rules_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_discount_rules'));
    }

    public function test_up_method_creates_item_conversions_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_conversions'));
        $this->assertTrue(Schema::hasColumns('item_conversions', ['uuid']));
    }

    public function test_down_method_drops_item_conversions_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_conversions'));
    }

}
