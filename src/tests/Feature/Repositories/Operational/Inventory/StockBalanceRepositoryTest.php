<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Operational\Inventory\Repositories\StockBalanceRepository;
use Tests\TestCase;

class StockBalanceRepositoryTest extends TestCase
{

    public function test_get_stock_balance_report(): void
    {
        $method = new \ReflectionMethod(StockBalanceRepository::class, 'getStockBalanceReport');
        $this->assertTrue($method->isPublic());
    }
}