<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\CustomerSupplierController;
use Tests\TestCase;

class CustomerSupplierControllerTest extends TestCase
{

    public function test_customers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierController::class, 'customers');
        $this->assertTrue($method->isPublic());
    }

    public function test_suppliers(): void
    {
        $method = new \ReflectionMethod(CustomerSupplierController::class, 'suppliers');
        $this->assertTrue($method->isPublic());
    }
}