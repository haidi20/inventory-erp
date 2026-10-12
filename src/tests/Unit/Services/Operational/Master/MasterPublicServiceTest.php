<?php

namespace Tests\Unit\Services\Operational\Master;

use App\Modules\Operational\Master\Services\MasterPublicService;
use Tests\TestCase;

class MasterPublicServiceTest extends TestCase
{

    public function test_find_product_by_uuid(): void
    {
        $method = new \ReflectionMethod(MasterPublicService::class, 'findProductByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_product_stock(): void
    {
        $method = new \ReflectionMethod(MasterPublicService::class, 'getProductStock');
        $this->assertTrue($method->isPublic());
    }

    public function test_decrement_product_stock(): void
    {
        $method = new \ReflectionMethod(MasterPublicService::class, 'decrementProductStock');
        $this->assertTrue($method->isPublic());
    }

    public function test_increment_product_stock(): void
    {
        $method = new \ReflectionMethod(MasterPublicService::class, 'incrementProductStock');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_employee_practitioner_id(): void
    {
        $method = new \ReflectionMethod(MasterPublicService::class, 'updateEmployeePractitionerId');
        $this->assertTrue($method->isPublic());
    }
}