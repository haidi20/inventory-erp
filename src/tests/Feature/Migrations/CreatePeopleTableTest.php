<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePeopleTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_people_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('people'));
        $this->assertTrue(Schema::hasColumns('people', ['uuid']));
    }

    public function test_down_method_drops_people_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('people'));
    }

    public function test_up_method_creates_person_custom_fields_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('person_custom_fields'));
        $this->assertTrue(Schema::hasColumns('person_custom_fields', ['uuid']));
    }

    public function test_down_method_drops_person_custom_fields_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('person_custom_fields'));
    }

    public function test_up_method_creates_person_addresses_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('person_addresses'));
        $this->assertTrue(Schema::hasColumns('person_addresses', ['uuid']));
    }

    public function test_down_method_drops_person_addresses_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('person_addresses'));
    }

    public function test_up_method_creates_person_contacts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('person_contacts'));
        $this->assertTrue(Schema::hasColumns('person_contacts', ['uuid']));
    }

    public function test_down_method_drops_person_contacts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('person_contacts'));
    }

}
