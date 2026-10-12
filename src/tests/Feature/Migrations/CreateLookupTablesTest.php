<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateLookupTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_lookup_types_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('lookup_types'));
        $this->assertTrue(Schema::hasColumns('lookup_types', ['uuid']));
    }

    public function test_up_method_creates_lookup_sets_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('lookup_sets'));
        $this->assertTrue(Schema::hasColumns('lookup_sets', ['uuid']));
    }

    public function test_up_method_creates_lookup_values_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('lookup_values'));
        $this->assertTrue(Schema::hasColumns('lookup_values', ['uuid']));
    }

    public function test_up_method_creates_lookup_value_details_table_and_columns(): void
    {
        $this->assertTrue(Schema::hasTable('lookup_value_details'));
        $this->assertTrue(Schema::hasColumns('lookup_value_details', ['uuid']));
    }
}

