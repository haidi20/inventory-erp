<?php

namespace Tests\Feature\Repositories\Operational\Procurement;

use App\Modules\Operational\Procurement\Repositories\PurchaseRepository;
use Tests\TestCase;

class PurchaseRepositoryTest extends TestCase
{

    public function test_save(): void
    {
        $method = new \ReflectionMethod(PurchaseRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_all(): void
    {
        $method = new \ReflectionMethod(PurchaseRepository::class, 'findAll');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(PurchaseRepository::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(PurchaseRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}