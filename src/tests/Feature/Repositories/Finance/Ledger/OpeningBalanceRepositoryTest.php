<?php

namespace Tests\Feature\Repositories\Finance\Ledger;

use App\Modules\Finance\Ledger\Repositories\OpeningBalanceRepository;
use Tests\TestCase;

class OpeningBalanceRepositoryTest extends TestCase
{

    public function test_get_opening_balance_header(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'getOpeningBalanceHeader');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_opening_balance_headers(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'getOpeningBalanceHeaders');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_opening_balance_header(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'createOpeningBalanceHeader');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_opening_balance_header(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'updateOpeningBalanceHeader');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_mapping_by_key(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'findMappingByKey');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_opening_balances(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'getOpeningBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_item_opening_balances(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'getItemOpeningBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_opening_balance(): void
    {
        $method = new \ReflectionMethod(OpeningBalanceRepository::class, 'updateOpeningBalance');
        $this->assertTrue($method->isPublic());
    }
}