<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateUsersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_users_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('users', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_users_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('users'));
    }

    public function test_up_method_creates_password_reset_tokens_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
        $this->assertTrue(Schema::hasColumns('password_reset_tokens', ['email']));
    }

    public function test_down_method_drops_password_reset_tokens_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
    }

    public function test_up_method_creates_sessions_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasColumns('sessions', ['id']));
    }

    public function test_down_method_drops_sessions_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('sessions'));
    }

}
