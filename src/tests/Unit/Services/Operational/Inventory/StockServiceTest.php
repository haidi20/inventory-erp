<?php

namespace Tests\Unit\Services\Operational\Inventory;

use App\Modules\Operational\Inventory\Services\StockService;
use Tests\TestCase;

class StockServiceTest extends TestCase
{

    public function test_value_list(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'valueList');
        $this->assertTrue($method->isPublic());
    }

    public function test_items_and_services(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'itemsAndServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_formula_bom(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'formulaBom');
        $this->assertTrue($method->isPublic());
    }

    public function test_units(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'units');
        $this->assertTrue($method->isPublic());
    }

    public function test_opening_balance(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'openingBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_item_conversion(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'itemConversion');
        $this->assertTrue($method->isPublic());
    }

    public function test_warehouses(): void
    {
        $method = new \ReflectionMethod(StockService::class, 'warehouses');
        $this->assertTrue($method->isPublic());
    }
}