<?php

namespace Tests\Unit\Services\Operational;

use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnItemEntity;
use App\Modules\Operational\Procurement\Repositories\PurchaseReturnRepositoryInterface;
use App\Modules\Operational\Procurement\Services\PurchaseReturnService;
use Mockery;
use Tests\TestCase;

class PurchaseReturnServiceTest extends TestCase
{
    protected $repository;
    protected PurchaseReturnService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(PurchaseReturnRepositoryInterface::class);
        $this->service = new PurchaseReturnService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_list_calls_repository(): void
    {
        $filter = new PurchaseReturnEntity(company_uuid: 'company-123');
        $this->repository->shouldReceive('getPageData')
            ->once()
            ->with($filter)
            ->andReturn(['items' => []]);

        $result = $this->service->getList($filter);
        $this->assertCount(0, $result);
    }

    public function test_get_detail_calls_repository(): void
    {
        $uuid = 'uuid-123';
        $branchUuid = 'branch-123';
        $companyUuid = 'company-123';
        $purchaseReturn = new PurchaseReturnEntity(uuid: $uuid, branch_uuid: $branchUuid, company_uuid: $companyUuid);

        $this->repository->shouldReceive('findByUUID')
            ->once()
            ->with($uuid, $branchUuid, $companyUuid)
            ->andReturn($purchaseReturn);

        $result = $this->service->getDetail($uuid, $branchUuid, $companyUuid);
        $this->assertSame($purchaseReturn, $result);
    }

    public function test_create_or_update_calls_save_and_find(): void
    {
        $entity = new PurchaseReturnEntity(
            branch_uuid: 'branch-123',
            company_uuid: 'company-123',
            supplier_uuid: 'supplier-123',
            location_uuid: 'wh-123',
            document_no: 'PR-001',
            date: time(),
            items: [
                new PurchaseReturnItemEntity(item_uuid: 'item-123', unit_uuid: 'unit-123', quantity: 2, price: 100),
            ],
            actor_uuid: 'actor-123',
        );

        $this->repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PurchaseReturnEntity::class));

        $this->repository->shouldReceive('findByUUID')
            ->once()
            ->with(Mockery::type('string'), $entity->branch_uuid, $entity->company_uuid)
            ->andReturn($entity);

        $result = $this->service->createOrUpdate($entity);
        $this->assertSame($entity, $result);
    }

    public function test_delete_calls_repository(): void
    {
        $entity = new PurchaseReturnEntity(
            uuid: 'uuid-123',
            branch_uuid: 'branch-123',
            company_uuid: 'company-123',
            actor_uuid: 'actor-123',
        );

        $this->repository->shouldReceive('delete')
            ->once()
            ->with($entity->uuid, $entity->branch_uuid, $entity->company_uuid, $entity->actor_uuid)
            ->andReturn(true);

        $result = $this->service->delete($entity);
        $this->assertTrue($result);
    }
}
