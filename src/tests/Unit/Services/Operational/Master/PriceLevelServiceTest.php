<?php

namespace Tests\Unit\Services\Operational\Master;

use App\Modules\Operational\Master\Services\PriceLevelService;
use Tests\TestCase;

class PriceLevelServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_active_levels(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'getActiveLevels');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(PriceLevelService::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }
}