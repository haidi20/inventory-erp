<?php

namespace Tests\Unit\Services\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Services\DoctorTaskService;
use Tests\TestCase;

class DoctorTaskServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(DoctorTaskService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}