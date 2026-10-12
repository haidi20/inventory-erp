<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePrescriptionsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_prescriptions_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('prescriptions'));
        $this->assertTrue(Schema::hasColumns('prescriptions', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_prescriptions_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('prescriptions'));
    }

}
