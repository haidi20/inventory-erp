<?php

namespace Tests\Unit\Services\Finance\Ledger;

use App\Modules\Finance\Ledger\Services\TrialBalanceQueryService;
use Tests\TestCase;

class TrialBalanceQueryServiceTest extends TestCase
{

    public function test_find_period_by_id(): void
    {
        $method = new \ReflectionMethod(TrialBalanceQueryService::class, 'findPeriodById');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balances_via_stored_procedure(): void
    {
        $method = new \ReflectionMethod(TrialBalanceQueryService::class, 'getBalancesViaStoredProcedure');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balances_by_account(): void
    {
        $method = new \ReflectionMethod(TrialBalanceQueryService::class, 'getBalancesByAccount');
        $this->assertTrue($method->isPublic());
    }
}