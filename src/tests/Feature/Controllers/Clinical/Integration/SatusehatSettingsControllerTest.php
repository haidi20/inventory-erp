<?php

namespace Tests\Feature\Controllers\Clinical\Integration;

use App\Modules\Clinical\Integration\Http\Controllers\SatusehatSettingsController;
use Tests\TestCase;

class SatusehatSettingsControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(SatusehatSettingsController::class, 'store');
        $this->assertTrue($method->isPublic());
    }
}