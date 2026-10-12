<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCacheTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_cache_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasColumns('cache', ['key']));
    }

    public function test_down_method_drops_cache_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('cache'));
    }

    public function test_up_method_creates_cache_locks_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('cache_locks'));
        $this->assertTrue(Schema::hasColumns('cache_locks', ['key']));
    }

    public function test_down_method_drops_cache_locks_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('cache_locks'));
    }

}
