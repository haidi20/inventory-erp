<?php

namespace Tests\Unit\Services\Operational\Master;

use App\Modules\Operational\Master\Services\CustomerSupplierService;
use Tests\TestCase;

class CustomerSupplierServiceTest extends TestCase
{

    public function test_customers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierService::class, 'customers');
        $this->assertTrue($method->isPublic());
    }

    public function test_suppliers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierService::class, 'suppliers');
        $this->assertTrue($method->isPublic());
    }
}