<?php

namespace Tests\Feature\Repositories\Operational\Master;

use App\Modules\Operational\Master\Repositories\CustomerSupplierRepository;
use Tests\TestCase;

class CustomerSupplierRepositoryTest extends TestCase
{

    public function test_get_customers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierRepository::class, 'getCustomers');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_suppliers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierRepository::class, 'getSuppliers');
        $this->assertTrue($method->isPublic());
    }
}