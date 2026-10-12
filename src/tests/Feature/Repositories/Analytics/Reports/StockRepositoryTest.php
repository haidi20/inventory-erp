<?php

namespace Tests\Feature\Repositories\Analytics\Reports;

use App\Modules\Analytics\Reports\Repositories\StockRepository;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{

    public function test_get_value_list(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getValueList');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_items_and_services(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getItemsAndServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_formula_bom(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getFormulaBom');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_units(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getUnits');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_opening_balance(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_item_conversions(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getItemConversions');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_warehouses(): void
    {
        $method = new \ReflectionMethod(StockRepository::class, 'getWarehouses');
        $this->assertTrue($method->isPublic());
    }
}