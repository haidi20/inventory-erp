<?php

namespace Tests\Unit\Services\Inventory;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;
use App\Modules\Operational\Manufacturing\Repositories\AssemblyRepositoryInterface;
use App\Modules\Operational\Manufacturing\Services\AssemblyService;
use Mockery;
use Tests\TestCase;

class AssemblyServiceTest extends TestCase
{
    private $repositoryMock;
    private AssemblyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(AssemblyRepositoryInterface::class);
        $this->service = new AssemblyService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository()
    {
        $entity = new AssemblyEntity(company_uuid: 'company-1');

        $this->repositoryMock->shouldReceive('index')->once()->with($entity)->andReturn(['items' => [], 'total' => 0]);
        $result = $this->service->index($entity);
        $this->assertArrayHasKey('items', $result);
    }

    public function test_post_updates_status_to_posted()
    {
        $assembly = new AssemblyEntity(uuid: 'as-123', status: 'pending');
        
        $this->repositoryMock->shouldReceive('findByID')->with('as-123')->andReturn($assembly);
        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (AssemblyEntity $entity) => $entity->uuid === 'as-123' && $entity->status === 'posted'))
            ->andReturn($assembly);

        $result = $this->service->post('as-123');
        $this->assertNotNull($result);
    }

    public function test_import_rows_saves_data()
    {
        $rows = [
            ['No Dokumen', 'Tanggal', 'Gudang', 'Item', 'Unit', 'Qty'],
            ['AS-001', '2026-04-01', 'WH-1', 'ITEM-1', 'UNIT-1', 10]
        ];

        $this->repositoryMock->shouldReceive('saveMany')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn(['total' => 1, 'success' => 1, 'failed' => 0, 'errors' => []]);

        $result = $this->service->importRows($rows, 'branch-1', 'company-1');

        $this->assertEquals(1, $result['success']);
    }
}
