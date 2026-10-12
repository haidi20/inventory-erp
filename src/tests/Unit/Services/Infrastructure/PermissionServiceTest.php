<?php

namespace Tests\Unit\Services\Infrastructure;

use Tests\TestCase;
use App\Modules\Core\Authorization\Services\PermissionService;
use App\Modules\Core\Authorization\Repositories\PermissionRepositoryInterface;
use App\Modules\Core\Authorization\Entities\PermissionEntity;
use App\Modules\Core\Authorization\Entities\PermissionFilterEntity;
use App\Modules\Core\Authorization\Entities\PermissionListResultEntity;
use Mockery;
use Mockery\MockInterface;

class PermissionServiceTest extends TestCase
{
    private PermissionService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(PermissionRepositoryInterface::class);
        $this->service = new PermissionService($this->repository);
    }

    /** @test */
    public function it_can_get_all_permissions()
    {
        $filter = new PermissionFilterEntity(page: 1, per_page: 10);
        $mockData = new PermissionListResultEntity(items: [], total: 0, per_page: 10, current_page: 1);

        $this->repository->shouldReceive('getPermissions')
            ->once()
            ->with($filter)
            ->andReturn($mockData);

        $result = $this->service->GetPermissions($filter);
        $this->assertEquals($mockData, $result);
    }

    /** @test */
    public function it_can_store_new_permission()
    {
        $entity = new PermissionEntity(
            name: 'View Reports',
            label: 'view_reports',
            created_by: 'user-uuid'
        );

        $this->repository->shouldReceive('store')
            ->once()
            ->with(Mockery::on(function (PermissionEntity $e) {
                return $e->name === 'view_reports' && $e->label === 'view_reports';
            }))
            ->andReturn(true);

        $result = $this->service->Store($entity);
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
