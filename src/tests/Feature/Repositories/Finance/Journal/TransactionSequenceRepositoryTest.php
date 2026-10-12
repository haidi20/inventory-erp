<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\TransactionSequenceRepository;
use Tests\TestCase;

class TransactionSequenceRepositoryTest extends TestCase
{

    public function test_get_and_lock(): void
    {
        $method = new \ReflectionMethod(TransactionSequenceRepository::class, 'getAndLock');
        $this->assertTrue($method->isPublic());
    }

    public function test_create(): void
    {
        $method = new \ReflectionMethod(TransactionSequenceRepository::class, 'create');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_and_lock(): void
    {
        $method = new \ReflectionMethod(TransactionSequenceRepository::class, 'findAndLock');
        $this->assertTrue($method->isPublic());
    }

    public function test_next_index(): void
    {
        $method = new \ReflectionMethod(TransactionSequenceRepository::class, 'nextIndex');
        $this->assertTrue($method->isPublic());
    }
}