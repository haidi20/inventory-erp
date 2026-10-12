<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Core\Identity\Entities\ActionResponseEntity;
use App\Modules\Operational\Master\Repositories\EmployeeRepositoryInterface;
use App\Modules\Core\Identity\Services\UserService;
use App\Modules\Operational\Master\Services\EmployeeService;
use Mockery;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    private $repositoryMock;
    private EmployeeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(EmployeeRepositoryInterface::class);
        $this->service = new EmployeeService($this->repositoryMock, Mockery::mock(UserService::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository()
    {
        $employee = EmployeeEntity::fromArray([
            'uuid' => 'employee-uuid',
            'branch_uuid' => 'branch-uuid',
            'company_uuid' => 'company-uuid',
            'name' => 'Dr. Index',
        ]);

        $this->repositoryMock->shouldReceive('getPageData')->once()->andReturn([
            'items' => [$employee],
            'total' => 1,
            'current_page' => 1,
        ]);
        
        $entity = EmployeeEntity::fromArray([
            'search' => 'dr',
            'branch_uuid' => 'branch-uuid',
            'company_uuid' => 'company-uuid',
        ]);
        $result = $this->service->index($entity);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result['items']);
        $this->assertSame('Dr. Index', $result['items'][0]->name);
        $this->assertSame(1, $result['total']);
    }

    public function test_store_validates_and_saves()
    {
        $entity = EmployeeEntity::fromArray([
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
            'name' => 'Dr. House',
            'role' => 'dokter',
            'fee_default' => 50
        ]);

        $this->repositoryMock->shouldReceive('save')->once()->andReturn($entity);

        $result = $this->service->store($entity);

        $this->assertSame('Dr. House', $result->name);
    }

    public function test_destroy_prevents_deletion_if_has_user()
    {
        $entity = EmployeeEntity::fromArray([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
        ]);
        $this->repositoryMock->shouldReceive('hasUser')->andReturn(true);

        $result = $this->service->destroy($entity);

        $this->assertInstanceOf(ActionResponseEntity::class, $result);
        $this->assertEquals('error', $result->status);
        $this->assertStringContainsString('akun user', $result->message);
    }

    public function test_destroy_deletes_employee_if_no_user()
    {
        $entity = EmployeeEntity::fromArray([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
        ]);
        $this->repositoryMock->shouldReceive('hasUser')->andReturn(false);
        $this->repositoryMock->shouldReceive('delete')->once();

        $result = $this->service->destroy($entity);

        $this->assertInstanceOf(ActionResponseEntity::class, $result);
        $this->assertEquals('success', $result->status);
    }
}
