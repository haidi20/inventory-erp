<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\PriceListController;
use Tests\TestCase;

class PriceListControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PriceListController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}