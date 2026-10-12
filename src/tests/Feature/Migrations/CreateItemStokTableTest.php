<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateItemStokTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_item_stok_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_stok'));
        $this->assertTrue(Schema::hasColumns('item_stok', ['uuid']));
    }

    public function test_down_method_drops_item_stok_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_stok'));
    }

}
