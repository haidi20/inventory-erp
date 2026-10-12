<?php

namespace Tests\Unit\Services\Inventory;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;
use App\Modules\Operational\Manufacturing\Repositories\BomRepositoryInterface;
use App\Modules\Operational\Manufacturing\Services\BomService;
use Mockery;
use Tests\TestCase;

class BomServiceTest extends TestCase
{
    private $repositoryMock;
    private BomService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(BomRepositoryInterface::class);
        $this->service = new BomService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository()
    {
        $entity = new BomEntity(company_uuid: 'company-1');

        $this->repositoryMock->shouldReceive('getPageData')->once()->with($entity)->andReturn(['items' => [], 'total' => 0]);
        $result = $this->service->index($entity);
        $this->assertArrayHasKey('items', $result);
    }

    public function test_save_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->save(new BomEntity(code: ''));
    }

    public function test_save_validates_finished_good()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->save(new BomEntity(code: 'BM-001', item_uuid: ''));
    }

    public function test_save_validates_details_exist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->save(new BomEntity(code: 'BM-001', item_uuid: 'FG-123', qty: 1, items: []));
    }

    public function test_save_calls_repository_on_success()
    {
        $input = BomEntity::fromArray([
            'code' => 'BM-001',
            'item_uuid' => 'FG-123',
            'qty' => 1,
            'items' => [
                ['item_uuid' => 'MAT-1', 'qty' => 10]
            ]
        ]);

        $this->repositoryMock->shouldReceive('save')->once()->with($input)->andReturn(new BomEntity(uuid: 'bom-1'));

        $result = $this->service->save($input);
        $this->assertNotNull($result);
    }
}
