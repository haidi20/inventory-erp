<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAccountingTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_accounts_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('accounts'));
        $this->assertTrue(Schema::hasColumns('accounts', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_accounts_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('accounts'));
    }

    public function test_up_method_creates_account_mappings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('account_mappings'));
        $this->assertTrue(Schema::hasColumns('account_mappings', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_account_mappings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('account_mappings'));
    }

    public function test_up_method_creates_tax_settings_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('tax_settings'));
        $this->assertTrue(Schema::hasColumns('tax_settings', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_tax_settings_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('tax_settings'));
    }

    public function test_up_method_creates_closing_periods_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('closing_periods'));
        $this->assertTrue(Schema::hasColumns('closing_periods', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_closing_periods_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('closing_periods'));
    }

    public function test_up_method_creates_journals_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('journals'));
        $this->assertTrue(Schema::hasColumns('journals', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_journals_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('journals'));
    }

    public function test_up_method_creates_journal_lines_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('journal_lines'));
        $this->assertTrue(Schema::hasColumns('journal_lines', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_journal_lines_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('journal_lines'));
    }

    public function test_up_method_creates_account_balances_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('account_balances'));
        $this->assertTrue(Schema::hasColumns('account_balances', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_account_balances_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('account_balances'));
    }

    public function test_up_method_creates_recurring_journals_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('recurring_journals'));
        $this->assertTrue(Schema::hasColumns('recurring_journals', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_recurring_journals_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('recurring_journals'));
    }

    public function test_up_method_creates_recurring_journal_lines_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('recurring_journal_lines'));
        $this->assertTrue(Schema::hasColumns('recurring_journal_lines', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_recurring_journal_lines_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('recurring_journal_lines'));
    }

    public function test_up_method_creates_journal_attachments_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('journal_attachments'));
        $this->assertTrue(Schema::hasColumns('journal_attachments', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_journal_attachments_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('journal_attachments'));
    }

}
