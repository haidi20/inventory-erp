<?php

namespace Tests\Unit\Services\Clinical\Ancillary;

use App\Modules\Clinical\Ancillary\Services\RadiologyOrderService;
use Tests\TestCase;

class RadiologyOrderServiceTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderService::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderService::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderService::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderService::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderService::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }
}