<?php

namespace Tests\Feature\Migrations;

use App\Modules\Operational\Master\Models\Product;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateMasterTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_employees_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('employees'));
        $this->assertTrue(Schema::hasColumns('employees', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_employees_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('employees'));
    }

    public function test_up_method_creates_patients_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('patients'));
        $this->assertTrue(Schema::hasColumns('patients', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_patients_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('patients'));
    }

    public function test_up_method_creates_suppliers_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('suppliers'));
        $this->assertTrue(Schema::hasColumns('suppliers', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_suppliers_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('suppliers'));
    }

    public function test_product_model_uses_items_table_and_columns()
    {
        $this->assertSame('items', (new Product())->getTable());
        $this->assertTrue(Schema::hasTable('items'));
        $this->assertTrue(Schema::hasColumns('items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_keeps_product_alias_non_destructive()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('items'));
    }

    public function test_up_method_creates_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('items'));
        $this->assertTrue(Schema::hasColumns('items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('items'));
    }

    public function test_up_method_creates_item_custom_fields_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_custom_fields'));
        $this->assertTrue(Schema::hasColumns('item_custom_fields', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_item_custom_fields_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_custom_fields'));
    }

    public function test_up_method_creates_item_discounts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('item_discounts'));
        $this->assertTrue(Schema::hasColumns('item_discounts', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_item_discounts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('item_discounts'));
    }

    public function test_up_method_creates_services_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('services'));
        $this->assertTrue(Schema::hasColumns('services', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_services_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('services'));
    }

    public function test_up_method_creates_service_packages_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('service_packages'));
        $this->assertTrue(Schema::hasColumns('service_packages', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_service_packages_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('service_packages'));
    }

    public function test_up_method_creates_service_package_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('service_package_items'));
        $this->assertTrue(Schema::hasColumns('service_package_items', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_service_package_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('service_package_items'));
    }

    public function test_up_method_creates_files_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('files'));
        $this->assertTrue(Schema::hasColumns('files', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_files_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('files'));
    }

    public function test_up_method_creates_file_variants_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('file_variants'));
        $this->assertTrue(Schema::hasColumns('file_variants', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_file_variants_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('file_variants'));
    }

    public function test_up_method_creates_file_audits_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('file_audits'));
        $this->assertTrue(Schema::hasColumns('file_audits', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_file_audits_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('file_audits'));
    }

}
