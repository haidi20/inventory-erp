<?php

namespace Tests\Feature\Controllers\Clinical\Integration;

use App\Modules\Clinical\Integration\Http\Controllers\BpjsSettingsController;
use Tests\TestCase;

class BpjsSettingsControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(BpjsSettingsController::class, 'store');
        $this->assertTrue($method->isPublic());
    }
}