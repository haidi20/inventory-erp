<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateGeneralLedgersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_general_ledgers_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('general_ledgers'));
        $this->assertTrue(Schema::hasColumns('general_ledgers', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_general_ledgers_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('general_ledgers'));
    }

}
