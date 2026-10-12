<?php

namespace Tests\Feature\Repositories\Operational\Procurement;

use App\Modules\Operational\Procurement\Repositories\PosPurchaseRepository;
use Tests\TestCase;

class PosPurchaseRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(PosPurchaseRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(PosPurchaseRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(PosPurchaseRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_invoice_no_exists(): void
    {
        $method = new \ReflectionMethod(PosPurchaseRepository::class, 'invoiceNoExists');
        $this->assertTrue($method->isPublic());
    }
}