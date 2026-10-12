<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\RecurringJournalRepository;
use Tests\TestCase;

class RecurringJournalRepositoryTest extends TestCase
{

    public function test_get_active_to_run(): void
    {
        $method = new \ReflectionMethod(RecurringJournalRepository::class, 'getActiveToRun');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(RecurringJournalRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }
}