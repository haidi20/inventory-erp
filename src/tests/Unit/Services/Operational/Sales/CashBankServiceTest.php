<?php

namespace Tests\Unit\Services\Operational\Sales;

use App\Modules\Operational\Sales\Services\CashBankService;
use Tests\TestCase;

class CashBankServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(CashBankService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}