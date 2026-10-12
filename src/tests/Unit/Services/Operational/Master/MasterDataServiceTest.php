<?php

namespace Tests\Unit\Services\Operational\Master;

use App\Modules\Operational\Master\Services\MasterDataService;
use Tests\TestCase;

class MasterDataServiceTest extends TestCase
{

    public function test_patients(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'patients');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_patient(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storePatient');
        $this->assertTrue($method->isPublic());
    }

    public function test_suppliers(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'suppliers');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storeSupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updateSupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'destroySupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_products(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'products');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_product(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storeProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_product(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updateProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_product(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'destroyProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_services(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'services');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_service(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storeService');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_service(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updateService');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_service(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'destroyService');
        $this->assertTrue($method->isPublic());
    }

    public function test_packages(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'packages');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_package(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storePackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_package(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updatePackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_package(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'destroyPackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_employees(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'employees');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storeEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updateEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'destroyEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_account(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'storeAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_account(): void
    {
        $method = new \ReflectionMethod(MasterDataService::class, 'updateAccount');
        $this->assertTrue($method->isPublic());
    }
}