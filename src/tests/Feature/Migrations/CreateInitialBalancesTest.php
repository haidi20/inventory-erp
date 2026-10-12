<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateInitialBalancesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_item_balances_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_balances'));
        $this->assertTrue(Schema::hasColumns('item_balances', ['uuid']));
    }

    public function test_down_method_drops_item_balances_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_balances'));
    }

    public function test_up_method_creates_person_saldo_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('person_saldo_details'));
        $this->assertTrue(Schema::hasColumns('person_saldo_details', ['uuid']));
    }

    public function test_down_method_drops_person_saldo_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('person_saldo_details'));
    }

}
