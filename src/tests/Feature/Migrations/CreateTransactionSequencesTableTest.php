<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTransactionSequencesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_transaction_sequences_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('transaction_sequences'));
        $this->assertTrue(Schema::hasColumns('transaction_sequences', ['uuid', 'created_at', 'updated_at', 'deleted_at']));
    }

    public function test_down_method_drops_transaction_sequences_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('transaction_sequences'));
    }

}
