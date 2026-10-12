<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebEmployeeController;
use Tests\TestCase;

class WebEmployeeControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebEmployeeController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}