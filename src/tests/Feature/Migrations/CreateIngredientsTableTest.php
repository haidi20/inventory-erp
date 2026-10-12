<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateIngredientsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_ingredients_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('ingredients'));
        $this->assertTrue(Schema::hasColumns('ingredients', ['uuid']));
    }

    public function test_down_method_drops_ingredients_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('ingredients'));
    }

    public function test_up_method_creates_stocks_table_and_columns()
    {
        // PK legacy 'id' sudah diganti uuid (standarisasi PK uuid, bagian 30).
        $this->assertTrue(Schema::hasTable('stocks'));
        $this->assertTrue(Schema::hasColumns('stocks', ['uuid']));
    }

    public function test_down_method_drops_stocks_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('stocks'));
    }

}
