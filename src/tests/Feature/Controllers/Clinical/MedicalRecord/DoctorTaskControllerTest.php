<?php

namespace Tests\Feature\Controllers\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Http\Controllers\DoctorTaskController;
use Tests\TestCase;

class DoctorTaskControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(DoctorTaskController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}