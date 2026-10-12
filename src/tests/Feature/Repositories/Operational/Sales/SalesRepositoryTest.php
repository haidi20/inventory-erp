<?php

namespace Tests\Feature\Repositories\Operational\Sales;

use App\Modules\Operational\Sales\Repositories\SalesRepository;
use Tests\TestCase;

class SalesRepositoryTest extends TestCase
{

    public function test_save(): void
    {
        $method = new \ReflectionMethod(SalesRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_all(): void
    {
        $method = new \ReflectionMethod(SalesRepository::class, 'findAll');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(SalesRepository::class, 'findByUUID');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(SalesRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}