<?php

namespace Tests\Unit\Services\Clinical\Ancillary;

use App\Modules\Clinical\Ancillary\Services\LabOrderService;
use Tests\TestCase;

class LabOrderServiceTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(LabOrderService::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(LabOrderService::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(LabOrderService::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(LabOrderService::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(LabOrderService::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }
}