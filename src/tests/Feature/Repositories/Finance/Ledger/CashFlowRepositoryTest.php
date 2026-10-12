<?php

namespace Tests\Feature\Repositories\Finance\Ledger;

use App\Modules\Finance\Ledger\Repositories\CashFlowRepository;
use Tests\TestCase;

class CashFlowRepositoryTest extends TestCase
{

    public function test_get_indirect_cash_flow(): void
    {
        $method = new \ReflectionMethod(CashFlowRepository::class, 'getIndirectCashFlow');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_beginning_cash_balance(): void
    {
        $method = new \ReflectionMethod(CashFlowRepository::class, 'getBeginningCashBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_ending_cash_balance(): void
    {
        $method = new \ReflectionMethod(CashFlowRepository::class, 'getEndingCashBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_depreciation_expense(): void
    {
        $method = new \ReflectionMethod(CashFlowRepository::class, 'getDepreciationExpense');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_direct_cash_flow(): void
    {
        $method = new \ReflectionMethod(CashFlowRepository::class, 'getDirectCashFlow');
        $this->assertTrue($method->isPublic());
    }
}