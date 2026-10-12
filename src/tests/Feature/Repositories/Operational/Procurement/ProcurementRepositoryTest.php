<?php

namespace Tests\Feature\Repositories\Operational\Procurement;

use App\Modules\Operational\Procurement\Repositories\ProcurementRepository;
use Tests\TestCase;

class ProcurementRepositoryTest extends TestCase
{

    public function test_build_purchase_query(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'buildPurchaseQuery');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_paginated_purchases(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'getPaginatedPurchases');
        $this->assertTrue($method->isPublic());
    }

    public function test_build_return_query(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'buildReturnQuery');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_paginated_returns(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'getPaginatedReturns');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_return(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'createReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_return_item(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'createReturnItem');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_purchase_by_uuid(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'findPurchaseByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_mark_purchase_received(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'markPurchaseReceived');
        $this->assertTrue($method->isPublic());
    }

    public function test_count_returns_today(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'countReturnsToday');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_return(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'deleteReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_return_by_uuid(): void
    {
        $method = new \ReflectionMethod(ProcurementRepository::class, 'findReturnByUuid');
        $this->assertTrue($method->isPublic());
    }
}