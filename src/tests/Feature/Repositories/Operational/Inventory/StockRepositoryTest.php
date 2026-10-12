<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Operational\Inventory\Repositories\StockRepository;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{

    public function test_get_stock_balance_report(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getStockBalanceReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_movement(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'saveMovement');
        $this->assertTrue($method->isPublic());
    }
}