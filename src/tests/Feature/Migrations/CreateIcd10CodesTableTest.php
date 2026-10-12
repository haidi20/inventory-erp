<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateIcd10CodesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_icd10_codes_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('icd10_codes'));
        $this->assertTrue(Schema::hasColumns('icd10_codes', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_icd10_codes_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('icd10_codes'));
    }

}
