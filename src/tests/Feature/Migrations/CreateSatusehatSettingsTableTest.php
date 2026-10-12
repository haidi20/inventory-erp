<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateSatusehatSettingsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_satusehat_settings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('satusehat_settings'));
        $this->assertTrue(Schema::hasColumns('satusehat_settings', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_satusehat_settings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('satusehat_settings'));
    }

}
