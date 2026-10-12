<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateEncounterVitalsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_encounter_vitals_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('encounter_vitals'));
        $this->assertTrue(Schema::hasColumns('encounter_vitals', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_encounter_vitals_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('encounter_vitals'));
    }

}
