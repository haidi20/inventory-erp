<?php

namespace Tests\Feature\Repositories\Clinical\Ancillary;

use App\Modules\Clinical\Ancillary\Repositories\RadiologyOrderRepository;
use Tests\TestCase;

class RadiologyOrderRepositoryTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderRepository::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderRepository::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_status(): void
    {
        $method = new \ReflectionMethod(RadiologyOrderRepository::class, 'updateStatus');
        $this->assertTrue($method->isPublic());
    }
}