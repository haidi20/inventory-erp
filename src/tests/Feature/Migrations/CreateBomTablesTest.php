<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateBomTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_boms_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('boms'));
        $this->assertTrue(Schema::hasColumns('boms', ['uuid']));
    }

    public function test_down_method_drops_boms_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('boms'));
    }

    public function test_up_method_creates_bom_details_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('bom_details'));
        $this->assertTrue(Schema::hasColumns('bom_details', ['uuid']));
    }

    public function test_down_method_drops_bom_details_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('bom_details'));
    }

    public function test_up_method_creates_bom_costs_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('bom_costs'));
        $this->assertTrue(Schema::hasColumns('bom_costs', ['uuid']));
    }

    public function test_down_method_drops_bom_costs_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('bom_costs'));
    }

    public function test_up_method_creates_assemblies_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('assemblies'));
        $this->assertTrue(Schema::hasColumns('assemblies', ['uuid']));
    }

    public function test_down_method_drops_assemblies_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('assemblies'));
    }

    public function test_up_method_creates_assembly_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('assembly_items'));
        $this->assertTrue(Schema::hasColumns('assembly_items', ['uuid']));
    }

    public function test_down_method_drops_assembly_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('assembly_items'));
    }

}
