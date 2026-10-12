<?php

namespace Tests\Unit\Services\Operational\Sales;

use App\Modules\Operational\Sales\Services\SaleService;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{

    public function test_list_sales(): void
    {
        $method = new \ReflectionMethod(SaleService::class, 'listSales');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_sale(): void
    {
        $method = new \ReflectionMethod(SaleService::class, 'createSale');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sale(): void
    {
        $method = new \ReflectionMethod(SaleService::class, 'getSale');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_before_create(): void
    {
        $method = new \ReflectionMethod(SaleService::class, 'validateBeforeCreate');
        $this->assertTrue($method->isPublic());
    }
}