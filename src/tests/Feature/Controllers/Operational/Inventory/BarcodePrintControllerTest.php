<?php

namespace Tests\Feature\Controllers\Operational\Inventory;

use App\Modules\Operational\Inventory\Http\Controllers\BarcodePrintController;
use Tests\TestCase;

class BarcodePrintControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BarcodePrintController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}