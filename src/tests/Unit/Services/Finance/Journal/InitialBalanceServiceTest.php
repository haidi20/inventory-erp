<?php

namespace Tests\Unit\Services\Finance\Journal;

use App\Modules\Finance\Journal\Services\InitialBalanceService;
use Tests\TestCase;

class InitialBalanceServiceTest extends TestCase
{

    public function test_get_item_balances(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'getItemBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_person_balances(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'getPersonBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_person_saldos(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'getPersonSaldos');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_item_balance(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'saveItemBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_person_balance(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'savePersonBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_item_balance(): void
    {
        $method = new \ReflectionMethod(InitialBalanceService::class, 'deleteItemBalance');
        $this->assertTrue($method->isPublic());
    }
}