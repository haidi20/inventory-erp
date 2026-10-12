<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\ComplaintController;
use Tests\TestCase;

class ComplaintControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(ComplaintController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(ComplaintController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(ComplaintController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(ComplaintController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(ComplaintController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}