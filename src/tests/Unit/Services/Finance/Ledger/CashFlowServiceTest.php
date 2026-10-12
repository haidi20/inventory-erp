<?php

namespace Tests\Unit\Services\Finance\Ledger;

use App\Modules\Finance\Ledger\Services\CashFlowService;
use Tests\TestCase;

class CashFlowServiceTest extends TestCase
{

    public function test_generate_indirect_method(): void
    {
        $method = new \ReflectionMethod(CashFlowService::class, 'generateIndirectMethod');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_direct_method(): void
    {
        $method = new \ReflectionMethod(CashFlowService::class, 'generateDirectMethod');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_comparative(): void
    {
        $method = new \ReflectionMethod(CashFlowService::class, 'generateComparative');
        $this->assertTrue($method->isPublic());
    }
}