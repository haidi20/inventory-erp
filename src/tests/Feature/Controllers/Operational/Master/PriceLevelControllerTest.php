<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\PriceLevelController;
use Tests\TestCase;

class PriceLevelControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PriceLevelController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(PriceLevelController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(PriceLevelController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(PriceLevelController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(PriceLevelController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}