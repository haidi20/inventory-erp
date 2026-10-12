<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterBranchScopeTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private array $tablesNeedingBranchUuid = [
        "employees",
        "suppliers",
        "payment_terms",
        "taxes",
    ];

    private array $tablesNeedingCompoundIndex = [
        "employees",
        "suppliers",
        "payment_terms",
        "taxes",
    ];

    public function test_master_tables_have_branch_uuid_column(): void
    {
        foreach ($this->tablesNeedingBranchUuid as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $this->assertContains(
                "branch_uuid",
                Schema::getColumnListing($table),
                "Table {$table} must have branch_uuid column."
            );
        }
    }

    public function test_master_tables_have_compound_index_with_branch(): void
    {
        foreach ($this->tablesNeedingBranchUuid as $table) {
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
                "Table {$table} must have compound index (company_uuid, branch_uuid, deleted_at)."
            );
        }
    }

    public function test_branch_uuid_is_nullable_uuid(): void
    {
        foreach ($this->tablesNeedingBranchUuid as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumns($table);
            $branchCol = collect($columns)->first(fn ($c) => $c["name"] === "branch_uuid");

            $this->assertNotNull($branchCol, "branch_uuid column not found in {$table}.");
        }
    }
}
