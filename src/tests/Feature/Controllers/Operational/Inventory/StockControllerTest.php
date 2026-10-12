<?php

namespace Tests\Feature\Controllers\Operational\Inventory;

use App\Modules\Operational\Inventory\Http\Controllers\StockController;
use Tests\TestCase;

class StockControllerTest extends TestCase
{

    public function test_show_value_list(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showValueList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_items_and_services(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showItemsAndServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_formula_bom(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showFormulaBom');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_unit_list(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showUnitList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_opening_balance(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_item_conversion(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showItemConversion');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_warehouse_list(): void
    {
        $method = new \ReflectionMethod(StockController::class, 'showWarehouseList');
        $this->assertTrue($method->isPublic());
    }
}