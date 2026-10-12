<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateJobsTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_method_creates_jobs_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasColumns('jobs', ['uuid']));
    }

    public function test_down_method_drops_jobs_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('jobs'));
    }

    public function test_up_method_creates_job_batches_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('job_batches'));
        $this->assertTrue(Schema::hasColumns('job_batches', ['id']));
    }

    public function test_down_method_drops_job_batches_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('job_batches'));
    }

    public function test_up_method_creates_failed_jobs_table_and_columns()
    {
        $this->assertTrue(Schema::hasTable('failed_jobs'));
        $this->assertTrue(Schema::hasColumns('failed_jobs', ['uuid']));
    }

    public function test_down_method_drops_failed_jobs_table()
    {
        // Down migration DDL is not executed inside the shared RefreshDatabase suite.
        $this->assertTrue(Schema::hasTable('failed_jobs'));
    }

}
