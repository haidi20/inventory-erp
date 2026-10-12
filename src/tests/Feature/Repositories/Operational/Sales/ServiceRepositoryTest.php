<?php

namespace Tests\Feature\Repositories\Operational\Sales;

use App\Modules\Operational\Sales\Repositories\ServiceRepository;
use Tests\TestCase;

class ServiceRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_code_exists(): void
    {
        $method = new \ReflectionMethod(ServiceRepository::class, 'codeExists');
        $this->assertTrue($method->isPublic());
    }
}