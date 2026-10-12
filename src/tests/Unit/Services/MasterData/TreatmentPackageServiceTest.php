<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepositoryInterface;
use App\Modules\Operational\Master\Services\TreatmentPackageService;
use Mockery;
use Tests\TestCase;

class TreatmentPackageServiceTest extends TestCase
{
    private TreatmentPackageRepositoryInterface $repositoryMock;
    private TreatmentPackageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(TreatmentPackageRepositoryInterface::class);
        $this->service = new TreatmentPackageService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository()
    {
        $item = TreatmentPackageEntity::fromArray([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
            'code' => 'PKG-001',
            'name' => 'Paket Tes',
        ]);

        $this->repositoryMock->shouldReceive('getPageData')->once()->andReturn([
            'items' => [$item],
            'total' => 1,
        ]);

        $result = $this->service->index(TreatmentPackageEntity::fromArray([
            'search' => 'paket',
            'branch_uuid' => '22222222-2222-2222-2222-222222222222',
            'company_uuid' => '33333333-3333-3333-3333-333333333333',
        ]));

        $this->assertIsArray($result);
        $this->assertCount(1, $result['items']);
        $this->assertSame('PKG-001', $result['items'][0]->code);
        $this->assertSame(1, $result['total']);
    }

    public function test_store_validates_and_saves()
    {
        $entity = TreatmentPackageEntity::fromArray([
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
            'code' => 'PKG-001',
            'name' => 'Paket Medical Checkup',
            'price' => 150000,
        ]);

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn($entity);

        $result = $this->service->store($entity);

        $this->assertSame('PKG-001', $result->code);
    }

    public function test_update_validates_and_saves()
    {
        $entity = TreatmentPackageEntity::fromArray([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
            'code' => 'PKG-001',
            'name' => 'Paket Updated',
            'price' => 200000,
        ]);

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn($entity);

        $result = $this->service->update($entity);

        $this->assertSame('Paket Updated', $result->name);
    }

    public function test_destroy_calls_repository_delete()
    {
        $entity = TreatmentPackageEntity::fromArray([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'branch_uuid' => '11111111-1111-1111-1111-111111111111',
            'company_uuid' => '22222222-2222-2222-2222-222222222222',
        ]);

        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $this->service->destroy($entity);

        $this->assertTrue(true);
    }
}
