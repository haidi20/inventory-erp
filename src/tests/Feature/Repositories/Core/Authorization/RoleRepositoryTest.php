<?php

namespace Tests\Feature\Repositories\Core\Authorization;

use App\Modules\Core\Authorization\Repositories\RoleRepository;
use Tests\TestCase;

class RoleRepositoryTest extends TestCase
{

    public function test_get_roles(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'getRoles');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_id(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'findById');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_name(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'findByName');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }

    public function test_sync_permissions(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'syncPermissions');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_all_permissions(): void
    {
        $method = new \ReflectionMethod(RoleRepository::class, 'getAllPermissions');
        $this->assertTrue($method->isPublic());
    }
}