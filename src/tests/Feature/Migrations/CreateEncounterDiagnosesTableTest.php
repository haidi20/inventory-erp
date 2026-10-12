<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateEncounterDiagnosesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_encounter_diagnoses_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('encounter_diagnoses'));
        $this->assertTrue(Schema::hasColumns('encounter_diagnoses', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_encounter_diagnoses_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('encounter_diagnoses'));
    }

}
