<?php

namespace Tests\Unit\Services\Operational;

use Tests\TestCase;
use App\Modules\Operational\Inventory\Services\StockTransferService;
use App\Modules\Operational\Inventory\Repositories\StockTransferRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Inventory\Entities\StockTransferEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferItemEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferListResultEntity;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class StockTransferServiceTest extends TestCase
{
    private StockTransferService $service;
    private MockInterface $repo;
    private MockInterface $stockRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(StockTransferRepositoryInterface::class);
        $this->stockRepo = Mockery::mock(StockRepositoryInterface::class);
        $this->service = new StockTransferService($this->repo, $this->stockRepo);
    }

    #[Test]
    public function it_can_get_list()
    {
        $filter = new StockTransferFilterEntity(page: 1, per_page: 10);
        $mockResult = new StockTransferListResultEntity(items: [], total: 0, per_page: 10, current_page: 1);
        
        $this->repo->shouldReceive('getPageData')->once()->with($filter)->andReturn($mockResult);
        $result = $this->service->GetList($filter);
        $this->assertInstanceOf(StockTransferListResultEntity::class, $result);
    }

    #[Test]
    public function it_fails_if_source_and_destination_warehouse_are_the_same()
    {
        $warehouseUuid = (string) Str::uuid();
        $entity = new StockTransferEntity(
            source_location_uuid: $warehouseUuid,
            destination_location_uuid: $warehouseUuid,
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("gudang asal dan tujuan tidak boleh sama");

        $this->service->CreateOrUpdate($entity, 'company-uuid');
    }

    #[Test]
    public function it_can_create_a_stock_transfer()
    {
        $branchUuid = (string) Str::uuid();
        $companyUuid = (string) Str::uuid();
        $items = [
            new StockTransferItemEntity(
                product_uuid: (string) Str::uuid(),
                qty: 10,
                unit_uuid: (string) Str::uuid()
            )
        ];

        $entity = new StockTransferEntity(
            branch_uuid: $branchUuid,
            source_location_uuid: (string) Str::uuid(),
            destination_location_uuid: (string) Str::uuid(),
            date: time(),
            document_no: 'PAG-2026-001',
            items: $items,
            actor_uuid: (string) Str::uuid()
        );

        $this->repo->shouldReceive('store')
            ->once()
            ->with(Mockery::type(StockTransferEntity::class))
            ->andReturn(new StockTransferEntity(uuid: 'pag-uuid', branch_uuid: $branchUuid, company_uuid: $companyUuid));

        $this->repo->shouldReceive('getByUuid')
            ->andReturn(new StockTransferEntity(uuid: 'pag-uuid', branch_uuid: $branchUuid, company_uuid: $companyUuid));

        $result = $this->service->CreateOrUpdate($entity, $companyUuid);
        $this->assertEquals('pag-uuid', $result->uuid);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
