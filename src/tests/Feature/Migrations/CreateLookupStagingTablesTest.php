<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateLookupStagingTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_temp_lovs_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('temp_lovs'));
        $this->assertTrue(Schema::hasColumns('temp_lovs', [
            'uuid',
            'lookup_value_uuid',
            'lookup_set_uuid',
            'program_code',
            'display_name',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_up_method_creates_temp_lov_details_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('temp_lov_details'));
        $this->assertTrue(Schema::hasColumns('temp_lov_details', ['uuid', 'temp_lov_uuid', 'idx']));
    }
}
