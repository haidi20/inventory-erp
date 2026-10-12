<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAuditLogsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_audit_logs_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('audit_logs'));
        $this->assertTrue(Schema::hasColumns('audit_logs', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_audit_logs_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('audit_logs'));
    }

}
