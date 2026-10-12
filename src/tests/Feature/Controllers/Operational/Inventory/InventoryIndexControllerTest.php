<?php

namespace Tests\Feature\Controllers\Operational\Inventory;

use App\Modules\Operational\Inventory\Http\Controllers\InventoryIndexController;
use Tests\TestCase;

class InventoryIndexControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(InventoryIndexController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}