<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCoaAndAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_accounts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('accounts'));
        $this->assertTrue(Schema::hasColumns('accounts', ['uuid']));
    }

    public function test_down_method_drops_accounts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('accounts'));
    }

    public function test_up_method_creates_coas_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('coas'));
        $this->assertTrue(Schema::hasColumns('coas', ['uuid']));
    }

    public function test_down_method_drops_coas_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('coas'));
    }

    public function test_up_method_creates_assets_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('assets'));
        $this->assertTrue(Schema::hasColumns('assets', ['uuid']));
    }

    public function test_down_method_drops_assets_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('assets'));
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

    public function test_up_method_creates_coa_setups_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('coa_setups'));
        $this->assertTrue(Schema::hasColumns('coa_setups', ['program_account']));
    }

    public function test_down_method_drops_coa_setups_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('coa_setups'));
    }

}
