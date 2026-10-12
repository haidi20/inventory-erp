<?php

namespace Tests\Unit\Services\Operational\Sales;

use App\Modules\Operational\Sales\Services\ServiceService;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{

    public function test_list_services(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'listServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_service(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'createService');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_service(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'getService');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_service(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'updateService');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_before_save(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'validateBeforeSave');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_service(): void
    {
        $method = new \ReflectionMethod(ServiceService::class, 'deleteService');
        $this->assertTrue($method->isPublic());
    }
}