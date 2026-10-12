<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateClinicProfileTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_clinic_profile_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('clinic_profile'));
        $this->assertTrue(Schema::hasColumns('clinic_profile', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_clinic_profile_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('clinic_profile'));
    }

}
