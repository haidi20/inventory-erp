<?php

namespace Tests\Feature\Controllers\Clinical\Ancillary;

use App\Modules\Clinical\Ancillary\Http\Controllers\RadiologyOrderController;
use Tests\TestCase;

class RadiologyOrderControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}