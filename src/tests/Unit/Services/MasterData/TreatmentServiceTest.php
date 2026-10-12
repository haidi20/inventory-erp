<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Operational\Master\Repositories\TreatmentRepositoryInterface;
use App\Modules\Operational\Master\Services\TreatmentService;
use Mockery;
use Tests\TestCase;

class TreatmentServiceTest extends TestCase
{
    private TreatmentRepositoryInterface $repositoryMock;
    private TreatmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(TreatmentRepositoryInterface::class);
        $this->service = new TreatmentService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository()
    {
        $item = TreatmentEntity::fromArray([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
            'code' => 'CONS-001',
            'name' => 'Konsultasi',
        ]);

        $this->repositoryMock->shouldReceive('getPageData')->once()->andReturn([
            'items' => [$item],
            'total' => 1,
        ]);

        $result = $this->service->index(TreatmentEntity::fromArray([
            'search' => 'konsul',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
        ]));

        $this->assertIsArray($result);
        $this->assertCount(1, $result['items']);
        $this->assertSame('CONS-001', $result['items'][0]->code);
        $this->assertSame(1, $result['total']);
    }

    public function test_store_validates_and_saves()
    {
        $entity = TreatmentEntity::fromArray([
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
            'code' => 'CONS-001',
            'name' => 'Konsultasi Umum',
            'price' => 50000,
        ]);

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn($entity);

        $result = $this->service->store($entity);

        $this->assertSame('CONS-001', $result->code);
    }

    public function test_update_validates_and_saves()
    {
        $entity = TreatmentEntity::fromArray([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
            'code' => 'CONS-001',
            'name' => 'Konsultasi Umum Updated',
            'price' => 60000,
        ]);

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn($entity);

        $result = $this->service->update($entity);

        $this->assertSame('Konsultasi Umum Updated', $result->name);
    }

    public function test_destroy_calls_repository_delete()
    {
        $entity = TreatmentEntity::fromArray([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
        ]);

        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $this->service->destroy($entity);

        $this->assertTrue(true);
    }
}
