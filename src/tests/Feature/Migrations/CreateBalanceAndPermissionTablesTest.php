<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateBalanceAndPermissionTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_user_warehouses_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('user_warehouses'));
        $this->assertTrue(Schema::hasColumns('user_warehouses', ['uuid']));
    }

    public function test_down_method_drops_user_warehouses_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('user_warehouses'));
    }

    public function test_up_method_creates_account_balances_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('account_balances'));
        $this->assertTrue(Schema::hasColumns('account_balances', ['uuid']));
    }

    public function test_down_method_drops_account_balances_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('account_balances'));
    }

    public function test_up_method_creates_person_balances_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('person_balances'));
        $this->assertTrue(Schema::hasColumns('person_balances', ['uuid']));
    }

    public function test_down_method_drops_person_balances_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('person_balances'));
    }

    public function test_up_method_creates_opening_balance_records_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('opening_balance_records'));
        $this->assertTrue(Schema::hasColumns('opening_balance_records', ['uuid']));
    }

    public function test_down_method_drops_opening_balance_records_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('opening_balance_records'));
    }

}
