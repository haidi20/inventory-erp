<?php

namespace Tests\Unit\Services\Operational\Procurement;

use App\Modules\Operational\Procurement\Services\PosPurchaseService;
use Tests\TestCase;

class PosPurchaseServiceTest extends TestCase
{

    public function test_list_purchases(): void
    {
        $method = new \ReflectionMethod(PosPurchaseService::class, 'listPurchases');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_purchase(): void
    {
        $method = new \ReflectionMethod(PosPurchaseService::class, 'createPurchase');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_before_create(): void
    {
        $method = new \ReflectionMethod(PosPurchaseService::class, 'validateBeforeCreate');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_purchase(): void
    {
        $method = new \ReflectionMethod(PosPurchaseService::class, 'getPurchase');
        $this->assertTrue($method->isPublic());
    }
}