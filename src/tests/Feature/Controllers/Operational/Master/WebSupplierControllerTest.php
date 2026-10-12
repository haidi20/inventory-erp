<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebSupplierController;
use Tests\TestCase;

class WebSupplierControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebSupplierController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}