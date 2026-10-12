<?php

namespace Tests\Unit\Services\Operational;

use Tests\TestCase;
use App\Modules\Operational\Procurement\Services\PurchaseOrderService;
use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepositoryInterface;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderItemEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderListResultEntity;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;

class PurchaseOrderServiceTest extends TestCase
{
    private PurchaseOrderService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(PurchaseOrderRepositoryInterface::class);
        $this->service = new PurchaseOrderService($this->repository);
    }

    /** @test */
    public function it_can_get_list()
    {
        $filter = new PurchaseOrderFilterEntity(page: 1, per_page: 10);
        $mockResult = new PurchaseOrderListResultEntity(items: [], total: 0, page: 1);
        
        $this->repository->shouldReceive('findAll')->once()->with($filter)->andReturn($mockResult);
        
        $result = $this->service->GetList($filter);
        $this->assertInstanceOf(PurchaseOrderListResultEntity::class, $result);
    }

    /** @test */
    public function it_can_create_a_purchase_order_with_items()
    {
        $actorUuid = (string) Str::uuid();
        $items = [
            new PurchaseOrderItemEntity(
                product_uuid: (string) Str::uuid(),
                unit_uuid: (string) Str::uuid(),
                quantity: 10,
                price: 5000,
            )
        ];

        $entity = new PurchaseOrderEntity(
            branch_uuid: (string) Str::uuid(),
            supplier_uuid: (string) Str::uuid(),
            location_uuid: (string) Str::uuid(),
            document_no: 'PO-2026-001',
            date: time(),
            actor_uuid: $actorUuid,
            items: $items
        );

        $mockPo = Mockery::mock(PurchaseOrder::class)->makePartial();
        $mockPo->uuid = 'po-uuid';

        $this->repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PurchaseOrderEntity::class))
            ->andReturn($mockPo);

        $result = $this->service->CreateOrUpdate($entity);
        $this->assertEquals('po-uuid', $result->uuid);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
