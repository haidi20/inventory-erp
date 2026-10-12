<?php

namespace Tests\Feature\Controllers\Clinical\Ancillary;

use App\Modules\Clinical\Ancillary\Http\Controllers\LabOrderController;
use Tests\TestCase;

class LabOrderControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(LabOrderController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(LabOrderController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(LabOrderController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(LabOrderController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(LabOrderController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}