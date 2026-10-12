<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\WebComplaintController;
use Tests\TestCase;

class WebComplaintControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebComplaintController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(WebComplaintController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(WebComplaintController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(WebComplaintController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}