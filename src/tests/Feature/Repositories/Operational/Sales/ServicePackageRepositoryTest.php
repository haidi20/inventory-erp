<?php

namespace Tests\Feature\Repositories\Operational\Sales;

use App\Modules\Operational\Sales\Repositories\ServicePackageRepository;
use Tests\TestCase;

class ServicePackageRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_code_exists(): void
    {
        $method = new \ReflectionMethod(ServicePackageRepository::class, 'codeExists');
        $this->assertTrue($method->isPublic());
    }
}