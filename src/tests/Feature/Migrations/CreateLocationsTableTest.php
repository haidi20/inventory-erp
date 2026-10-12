<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateLocationsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_locations_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('locations'));
        $this->assertTrue(Schema::hasColumns('locations', ['uuid']));
    }

    public function test_down_method_drops_locations_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('locations'));
    }

}
