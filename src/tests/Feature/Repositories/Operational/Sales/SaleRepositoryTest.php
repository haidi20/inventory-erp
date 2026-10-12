<?php

namespace Tests\Feature\Repositories\Operational\Sales;

use App\Modules\Operational\Sales\Repositories\SaleRepository;
use Tests\TestCase;

class SaleRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(SaleRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(SaleRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(SaleRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_sale_no_exists(): void
    {
        $method = new \ReflectionMethod(SaleRepository::class, 'saleNoExists');
        $this->assertTrue($method->isPublic());
    }
}