<?php

namespace Tests\Unit\Services\Operational\Inventory;

use App\Modules\Operational\Inventory\Services\PriceListService;
use Tests\TestCase;

class PriceListServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(PriceListService::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}