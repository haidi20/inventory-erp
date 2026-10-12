<?php

namespace Tests\Feature\Controllers\Core\Platform;

use App\Modules\Core\Platform\Http\Controllers\HimbauanController;
use Tests\TestCase;

class HimbauanControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(HimbauanController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}