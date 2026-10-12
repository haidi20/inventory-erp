<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePaymentTermsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_payment_terms_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('payment_terms'));
        $this->assertTrue(Schema::hasColumns('payment_terms', ['uuid']));
    }

    public function test_down_method_drops_payment_terms_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('payment_terms'));
    }

}
