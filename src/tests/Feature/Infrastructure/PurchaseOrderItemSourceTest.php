<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseOrderItemSourceTest extends TestCase
{
    public function test_purchase_order_items_has_purchase_requisition_uuid_column(): void
    {
        $this->assertTrue(Schema::hasColumn('purchase_order_items', 'purchase_requisition_uuid'));
    }

    public function test_purchase_requisition_uuid_is_nullable(): void
    {
        $columns = Schema::getColumns('purchase_order_items');
        $column = collect($columns)->firstWhere('name', 'purchase_requisition_uuid');

        $this->assertNotNull($column);
        $this->assertTrue($column['nullable']);
    }

    public function test_purchase_requisition_uuid_has_foreign_key(): void
    {
        $foreignKeys = Schema::getForeignKeys('purchase_order_items');
        $fk = collect($foreignKeys)->first(fn ($fk) => in_array('purchase_requisition_uuid', $fk['columns']));

        $this->assertNotNull($fk, 'FK purchase_requisition_uuid tidak ditemukan');
        $this->assertSame('purchase_requisitions', $fk['foreign_table']);
    }

    public function test_purchase_requisition_uuid_has_index(): void
    {
        $indexes = Schema::getIndexes('purchase_order_items');
        $index = collect($indexes)->first(fn ($idx) => in_array('purchase_requisition_uuid', $idx['columns']));

        $this->assertNotNull($index, 'Index purchase_requisition_uuid tidak ditemukan');
    }
}