<?php

namespace Tests\Unit\Services\Clinical\Registration;

use App\Modules\Clinical\Registration\Services\PatientService;
use Tests\TestCase;

class PatientServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(PatientService::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}