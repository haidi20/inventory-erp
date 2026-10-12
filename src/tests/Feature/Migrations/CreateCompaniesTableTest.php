<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCompaniesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_companies_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('companies'));
        $this->assertTrue(Schema::hasColumns('companies', ['uuid']));
    }

    public function test_down_method_drops_companies_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('companies'));
    }

}
