<?php

namespace Tests\Unit\Services\Clinical\Integration;

use App\Modules\Clinical\Integration\Services\SatusehatMenuService;
use Tests\TestCase;

class SatusehatMenuServiceTest extends TestCase
{

    public function test_overview(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuService::class, 'overview');
        $this->assertTrue($method->isPublic());
    }

    public function test_sync(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuService::class, 'sync');
        $this->assertTrue($method->isPublic());
    }

    public function test_encounter(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuService::class, 'encounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_fhir(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuService::class, 'fhir');
        $this->assertTrue($method->isPublic());
    }

    public function test_config(): void
    {
        $method = new \ReflectionMethod(SatusehatMenuService::class, 'config');
        $this->assertTrue($method->isPublic());
    }
}