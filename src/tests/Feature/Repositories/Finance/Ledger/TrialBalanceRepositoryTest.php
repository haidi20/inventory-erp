<?php

namespace Tests\Feature\Repositories\Finance\Ledger;

use App\Modules\Finance\Ledger\Repositories\TrialBalanceRepository;
use Tests\TestCase;

class TrialBalanceRepositoryTest extends TestCase
{

    public function test_get_balances_by_account(): void
    {
        $method = new \ReflectionMethod(TrialBalanceRepository::class, 'getBalancesByAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balances_via_stored_procedure(): void
    {
        $method = new \ReflectionMethod(TrialBalanceRepository::class, 'getBalancesViaStoredProcedure');
        $this->assertTrue($method->isPublic());
    }
}