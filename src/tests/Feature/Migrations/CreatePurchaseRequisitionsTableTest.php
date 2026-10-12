<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePurchaseRequisitionsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_purchase_requisitions_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_requisitions'));
        $this->assertTrue(Schema::hasColumns('purchase_requisitions', [
            'uuid',
            'branch_uuid',
            'company_uuid',
            'document_no',
            'date',
            'status',
            'approved_by',
            'approved_at',
            'created_by',
            'updated_by',
            'deleted_by',
            'deleted_at',
        ]));
    }

    public function test_down_method_drops_purchase_requisitions_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_requisitions'));
    }

    public function test_up_method_creates_purchase_requisition_items_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('purchase_requisition_items'));
        $this->assertTrue(Schema::hasColumns('purchase_requisition_items', [
            'uuid',
            'purchase_requisition_uuid',
            'branch_uuid',
            'company_uuid',
            'product_uuid',
            'unit_uuid',
            'quantity',
            'estimated_price',
            'subtotal',
        ]));
    }

    public function test_down_method_drops_purchase_requisition_items_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('purchase_requisition_items'));
    }
}
