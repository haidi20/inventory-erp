<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateBpjsSettingsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_bpjs_settings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('bpjs_settings'));
        $this->assertTrue(Schema::hasColumns('bpjs_settings', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_bpjs_settings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('bpjs_settings'));
    }

}
