<?php

namespace Tests\Unit\Services\Clinical\Registration;

use App\Modules\Clinical\Registration\Services\RegistrationPublicService;
use Tests\TestCase;

class RegistrationPublicServiceTest extends TestCase
{

    public function test_get_active_patients(): void
    {
        $method = new \ReflectionMethod(RegistrationPublicService::class, 'getActivePatients');
        $this->assertTrue($method->isPublic());
    }
}