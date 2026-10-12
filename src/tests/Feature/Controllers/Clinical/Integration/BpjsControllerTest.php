<?php

namespace Tests\Feature\Controllers\Clinical\Integration;

use App\Modules\Clinical\Integration\Http\Controllers\BpjsController;
use Tests\TestCase;

class BpjsControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BpjsController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}