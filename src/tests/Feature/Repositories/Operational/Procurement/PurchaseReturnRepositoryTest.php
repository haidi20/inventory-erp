<?php

namespace Tests\Feature\Repositories\Operational\Procurement;

use App\Modules\Operational\Procurement\Repositories\PurchaseReturnRepository;
use Tests\TestCase;

class PurchaseReturnRepositoryTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(PurchaseReturnRepository::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(PurchaseReturnRepository::class, 'findByUUID');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(PurchaseReturnRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(PurchaseReturnRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}