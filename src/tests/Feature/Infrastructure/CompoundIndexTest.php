<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompoundIndexTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private array $criticalTables = [
        "items",
        "patients",
        "item_stok",
        "sales_invoices",
        "purchase_orders",
        "general_ledgers",
    ];

    public function test_critical_tables_have_compound_index(): void
    {
        foreach ($this->criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $indexes = Schema::getIndexes($table);

            $hasCompound = collect($indexes)->contains(function ($index) {
                $columns = $index["columns"];
                return in_array("company_uuid", $columns)
                    && in_array("branch_uuid", $columns)
                    && in_array("deleted_at", $columns)
                    && count($columns) >= 3;
            });

            $this->assertTrue(
                $hasCompound,
                "Table \" . $table . \" must have compound index (company_uuid, branch_uuid, deleted_at)."
            );
        }
    }

    public function test_critical_tables_have_deleted_at_single_index(): void
    {
        foreach ($this->criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $indexes = Schema::getIndexes($table);

            $hasDeletedAt = collect($indexes)->contains(function ($index) {
                return $index["columns"] === ["deleted_at"];
            });

            $this->assertTrue(
                $hasDeletedAt,
                "Table \" . $table . \" must have single index on deleted_at."
            );
        }
    }
}
