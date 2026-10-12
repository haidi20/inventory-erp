<?php

namespace Tests\Unit\Services\Operational\Sales;

use App\Modules\Operational\Sales\Services\ServicePackageService;
use Tests\TestCase;

class ServicePackageServiceTest extends TestCase
{

    public function test_list_packages(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'listPackages');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_package(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'createPackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_package(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'getPackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_package(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'updatePackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_before_save(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'validateBeforeSave');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_package(): void
    {
        $method = new \ReflectionMethod(ServicePackageService::class, 'deletePackage');
        $this->assertTrue($method->isPublic());
    }
}