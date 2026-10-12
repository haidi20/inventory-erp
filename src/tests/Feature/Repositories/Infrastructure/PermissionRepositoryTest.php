<?php

namespace Tests\Feature\Repositories\Infrastructure;

use App\Modules\Core\Authorization\Repositories\PermissionRepository;
use App\Modules\Core\Authorization\Entities\PermissionEntity;
use App\Modules\Core\Authorization\Entities\PermissionFilterEntity;
use App\Modules\Core\Authorization\Entities\PermissionListResultEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository();
    }

    /** @test */
    public function it_can_store_a_permission()
    {
        $entity = new PermissionEntity(
            uuid: (string) Str::uuid(),
            name: 'view_dashboard',
            label: 'View Dashboard'
        );

        $result = $this->repository->store($entity);

        $this->assertTrue($result);
        $this->assertDatabaseHas('permissions', ['name' => 'view_dashboard']);
    }

    /** @test */
    public function it_can_get_paginated_permissions_as_entity()
    {
        for ($i = 0; $i < 15; $i++) {
            DB::table('permissions')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => "permission_$i",
                'label' => "Label $i",
                'guard_name' => 'api',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }

        $filter = new PermissionFilterEntity(page: 1, per_page: 10);
        $result = $this->repository->getPermissions($filter);

        $this->assertInstanceOf(PermissionListResultEntity::class, $result);
        $this->assertCount(10, $result->items);
        $this->assertEquals(15, $result->total);
    }
}

