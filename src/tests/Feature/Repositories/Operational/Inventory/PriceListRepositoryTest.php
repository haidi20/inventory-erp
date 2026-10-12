<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Operational\Inventory\Repositories\PriceListRepository;
use Tests\TestCase;

class PriceListRepositoryTest extends TestCase
{

    public function test_get_products(): void
    {
        $method = new \ReflectionMethod(PriceListRepository::class, 'getProducts');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_services(): void
    {
        $method = new \ReflectionMethod(PriceListRepository::class, 'getServices');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_packages(): void
    {
        $method = new \ReflectionMethod(PriceListRepository::class, 'getPackages');
        $this->assertTrue($method->isPublic());
    }
}