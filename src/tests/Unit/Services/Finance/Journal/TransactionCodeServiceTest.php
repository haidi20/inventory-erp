<?php

namespace Tests\Unit\Services\Finance\Journal;

use App\Modules\Finance\Journal\Services\TransactionCodeService;
use Tests\TestCase;

class TransactionCodeServiceTest extends TestCase
{

    public function test_generate(): void
    {
        $method = new \ReflectionMethod(TransactionCodeService::class, 'generate');
        $this->assertTrue($method->isPublic());
    }
}