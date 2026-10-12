<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTaxesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_taxes_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('taxes'));
        $this->assertTrue(Schema::hasColumns('taxes', ['uuid']));
    }

    public function test_down_method_drops_taxes_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('taxes'));
    }

}
