<?php

namespace Tests\Unit\Services\Operational\Procurement;

use App\Modules\Operational\Procurement\Services\ProcurementPublicService;
use Tests\TestCase;

class ProcurementPublicServiceTest extends TestCase
{

    public function test_find_purchase_by_uuid(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'findPurchaseByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_mark_purchase_received(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'markPurchaseReceived');
        $this->assertTrue($method->isPublic());
    }

    public function test_count_returns_today(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'countReturnsToday');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_return(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'deleteReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_return_by_uuid(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'findReturnByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_return(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'createReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_return_item(): void
    {
        $method = new \ReflectionMethod(ProcurementPublicService::class, 'createReturnItem');
        $this->assertTrue($method->isPublic());
    }
}