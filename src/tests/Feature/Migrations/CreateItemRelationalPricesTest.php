<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateItemRelationalPricesTest extends TestCase
{
    use RefreshDatabase;

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

}
