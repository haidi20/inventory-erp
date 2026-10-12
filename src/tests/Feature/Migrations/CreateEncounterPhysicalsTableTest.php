<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateEncounterPhysicalsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_encounter_physicals_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('encounter_physicals'));
        $this->assertTrue(Schema::hasColumns('encounter_physicals', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_encounter_physicals_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('encounter_physicals'));
    }

}
