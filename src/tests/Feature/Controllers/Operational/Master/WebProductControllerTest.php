<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebProductController;
use Tests\TestCase;

class WebProductControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebProductController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}