<?php

namespace Tests\Feature\Accounting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_critical_tables_exist_after_migration(): void
    {
        foreach ([
            'branches',
            'users',
            'patients',
            'audit_logs',
            'satusehat_settings',
            'journals',
            'journal_lines',
            'account_balances',
            'closing_periods',
            'migrations',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} should exist after migration.");
        }
    }

    public function test_critical_columns_exist_for_key_modules(): void
    {
        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'branch_uuid',
            'user_uuid',
            'event',
            'entity_type',
            'entity_uuid',
            'before',
            'after',
        ]));

        $this->assertTrue(Schema::hasColumns('satusehat_settings', [
            'org_uuid',
            'client_uuid',
            'client_secret',
            'base_url',
            'auth_url',
            'is_active',
        ]));

        $this->assertTrue(Schema::hasColumns('account_balances', [
            'branch_uuid',
            'account_uuid',
            'period',
            'opening_balance',
            'closing_balance',
        ]));
    }

    public function test_migration_seeded_singleton_settings_and_history_table(): void
    {
        $this->assertEquals(1, DB::table('satusehat_settings')->count());
        $this->assertGreaterThan(0, DB::table('migrations')->count());
    }
}
