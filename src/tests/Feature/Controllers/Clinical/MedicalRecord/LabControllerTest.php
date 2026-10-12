<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\LabController;
use Tests\TestCase;

class LabControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_input_hasil(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'inputHasil');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(LabController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}