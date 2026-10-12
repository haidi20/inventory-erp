<?php

namespace Tests\Unit\Services\Clinical\Registration;

use App\Modules\Clinical\Registration\Services\RegistrationService;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{

    public function test_list_registrations(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'listRegistrations');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_registration(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'createRegistration');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_registration(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'getRegistration');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_registration(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'updateRegistration');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_references(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'validateReferences');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_registration(): void
    {
        $method = new \ReflectionMethod(RegistrationService::class, 'deleteRegistration');
        $this->assertTrue($method->isPublic());
    }
}