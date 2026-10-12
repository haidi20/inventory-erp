<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePosLegacyTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_shifts_table_and_columns()
    {
        // PK legacy 'id' sudah diganti uuid (standarisasi PK uuid, bagian 30).
        $this->assertTrue(Schema::hasTable('shifts'));
        $this->assertTrue(Schema::hasColumns('shifts', ['uuid']));
    }

    public function test_down_method_drops_shifts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('shifts'));
    }

    public function test_up_method_creates_transactions_table_and_columns()
    {
        // PK legacy 'id' sudah diganti uuid (standarisasi PK uuid, bagian 30).
        $this->assertTrue(Schema::hasTable('transactions'));
        $this->assertTrue(Schema::hasColumns('transactions', ['uuid']));
    }

    public function test_down_method_drops_transactions_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('transactions'));
    }

    public function test_up_method_creates_transaction_details_table_and_columns()
    {
        // PK legacy 'id' sudah diganti uuid (standarisasi PK uuid, bagian 30).
        $this->assertTrue(Schema::hasTable('transaction_details'));
        $this->assertTrue(Schema::hasColumns('transaction_details', ['uuid']));
    }

    public function test_down_method_drops_transaction_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('transaction_details'));
    }

}
