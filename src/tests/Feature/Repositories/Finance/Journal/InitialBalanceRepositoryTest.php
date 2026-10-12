<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\InitialBalanceRepository;
use Tests\TestCase;

class InitialBalanceRepositoryTest extends TestCase
{

    public function test_get_initial_balances(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'getInitialBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_item(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'findByItem');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_item_balance(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'saveItemBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_person_balances(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'getPersonBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_person_saldo(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'getPersonSaldo');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_person_balance(): void
    {
        $method = new \ReflectionMethod(InitialBalanceRepository::class, 'savePersonBalance');
        $this->assertTrue($method->isPublic());
    }
}