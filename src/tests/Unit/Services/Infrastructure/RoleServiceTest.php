<?php

namespace Tests\Unit\Services\Infrastructure;

use Tests\TestCase;
use App\Modules\Core\Authorization\Services\RoleService;
use App\Modules\Core\Authorization\Repositories\RoleRepositoryInterface;
use App\Modules\Core\Authorization\Entities\RoleEntity;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;

class RoleServiceTest extends TestCase
{
    private RoleService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RoleRepositoryInterface::class);
        $this->service = new RoleService($this->repository);
    }

    /** @test */
    public function it_can_get_roles()
    {
        $filters = new RoleEntity();
        $mockData = ['data' => [], 'total' => 0];

        $this->repository->shouldReceive('getRoles')
            ->once()
            ->with($filters)
            ->andReturn($mockData);

        $result = $this->service->GetRoles($filters);
        $this->assertEquals($mockData, $result);
    }

    /** @test */
    public function it_can_store_role()
    {
        $entity = new RoleEntity(
            name: 'Admin Role',
            label: 'Admin Label',
            actor_uuid: (string) Str::uuid()
        );

        $mockRole = new RoleEntity(uuid: (string) Str::uuid(), name: 'Admin Role');

        $this->repository->shouldReceive('store')
            ->once()
            ->with(Mockery::type(RoleEntity::class))
            ->andReturn($mockRole);

        $result = $this->service->Store($entity);
        $this->assertInstanceOf(RoleEntity::class, $result);
    }

    /** @test */
    public function it_can_update_role()
    {
        $uuid = (string) Str::uuid();
        $entity = new RoleEntity(
            uuid: $uuid,
            name: 'Updated Role',
            label: 'Updated Label',
            actor_uuid: (string) Str::uuid()
        );

        $mockRole = new RoleEntity(uuid: $uuid, name: 'Updated Role');

        $this->repository->shouldReceive('findById')
            ->once()
            ->with($uuid)
            ->andReturn($mockRole);

        $this->repository->shouldReceive('update')
            ->once()
            ->with(Mockery::type(RoleEntity::class))
            ->andReturn($mockRole);

        $result = $this->service->Update($entity);
        $this->assertInstanceOf(RoleEntity::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
