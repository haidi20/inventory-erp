<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PivotAuditColumnsTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_user_companies_has_audit_columns(): void
    {
        $columns = Schema::getColumnListing("user_companies");

        $this->assertContains("updated_at", $columns, "user_companies must have updated_at.");
        $this->assertContains("updated_by", $columns, "user_companies must have updated_by.");
        $this->assertContains("deleted_by", $columns, "user_companies must have deleted_by.");
        $this->assertContains("deleted_at", $columns, "user_companies must have deleted_at.");
    }

    public function test_user_branches_has_audit_columns(): void
    {
        $columns = Schema::getColumnListing("user_branches");

        $this->assertContains("updated_at", $columns, "user_branches must have updated_at.");
        $this->assertContains("updated_by", $columns, "user_branches must have updated_by.");
        $this->assertContains("deleted_by", $columns, "user_branches must have deleted_by.");
        $this->assertContains("deleted_at", $columns, "user_branches must have deleted_at.");
    }

    public function test_pivot_deleted_at_is_bigInteger_with_default_zero(): void
    {
        $this->assertTrue(
            Schema::hasColumn("user_companies", "deleted_at"),
            "user_companies.deleted_at must exist."
        );

        $columns = Schema::getColumns("user_companies");
        $deletedAt = collect($columns)->first(fn ($c) => $c["name"] === "deleted_at");

        $this->assertNotNull($deletedAt, "deleted_at column not found in user_companies.");
        $this->assertTrue(
            str_contains($deletedAt["type"], "integer") || str_contains($deletedAt["type"], "bigint"),
            "deleted_at must be bigInteger type, got: " . $deletedAt["type"]
        );
    }
}
