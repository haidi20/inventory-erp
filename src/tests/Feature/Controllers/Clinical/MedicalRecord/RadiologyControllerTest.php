<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\RadiologyController;
use Tests\TestCase;

class RadiologyControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_input_hasil(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'inputHasil');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(RadiologyController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}