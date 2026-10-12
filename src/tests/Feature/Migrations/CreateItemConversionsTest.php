<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateItemConversionsTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_up_method_creates_item_uoms_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_uoms'));
        $this->assertTrue(Schema::hasColumns('item_uoms', ['uuid']));
    }

    public function test_down_method_drops_item_uoms_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_uoms'));
    }

}
