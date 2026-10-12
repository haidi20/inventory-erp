<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class StandardizeAccountingColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan migrasi v1
    }

    /** @test */
    public function accounts_table_uses_uuid_suffix()
    {
        $this->assertTrue(Schema::hasColumn('accounts', 'branch_uuid'));
        $this->assertTrue(Schema::hasColumn('accounts', 'company_uuid'));
        $this->assertFalse(Schema::hasColumn('accounts', 'branch_id'));
        $this->assertFalse(Schema::hasColumn('accounts', 'company_id'));
    }

    /** @test */
    public function journals_table_uses_uuid_suffix()
    {
        $this->assertTrue(Schema::hasColumn('journals', 'branch_uuid'));
        $this->assertTrue(Schema::hasColumn('journals', 'closing_period_uuid'));
        $this->assertFalse(Schema::hasColumn('journals', 'branch_id'));
        $this->assertFalse(Schema::hasColumn('journals', 'closing_period_id'));
    }

    /** @test */
    public function journal_lines_table_uses_uuid_suffix()
    {
        $this->assertTrue(Schema::hasColumn('journal_lines', 'branch_uuid'));
        $this->assertTrue(Schema::hasColumn('journal_lines', 'journal_uuid'));
        $this->assertTrue(Schema::hasColumn('journal_lines', 'account_uuid'));
        $this->assertFalse(Schema::hasColumn('journal_lines', 'branch_id'));
        $this->assertFalse(Schema::hasColumn('journal_lines', 'journal_id'));
        $this->assertFalse(Schema::hasColumn('journal_lines', 'account_id'));
    }
}
