<?php

namespace Tests\Unit\Services\Operational;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseListResultEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepositoryInterface;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Operational\Procurement\Services\PurchaseService;
use App\Modules\Operational\Inventory\Services\StockBalanceService;
use Mockery;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    protected $repository;
    protected $stockService;
    protected $journalService;
    protected PurchaseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(PurchaseRepositoryInterface::class);
        $this->stockService = Mockery::mock(StockBalanceService::class);
        $this->journalService = Mockery::mock(JournalService::class);
        $this->service = new PurchaseService($this->repository, $this->stockService, $this->journalService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_list_calls_repository(): void
    {
        $filter = new PurchaseFilterEntity(branch_uuid: 'B001');
        $this->repository->shouldReceive('findAll')
            ->once()
            ->with($filter)
            ->andReturn(new PurchaseListResultEntity([], 0, 15, 1));

        $result = $this->service->getList($filter);
        $this->assertSame(0, $result->total);
    }

    public function test_get_detail_calls_repository(): void
    {
        $purchase = new PurchaseRecordEntity(uuid: 'uuid-123', branch_uuid: 'branch-123', company_uuid: 'company-123');

        $this->repository->shouldReceive('findByUuid')
            ->once()
            ->with($purchase->uuid, $purchase->branch_uuid, $purchase->company_uuid)
            ->andReturn($purchase);

        $result = $this->service->show($purchase->uuid, $purchase->branch_uuid, $purchase->company_uuid);
        $this->assertSame($purchase, $result);
    }

    public function test_create_purchase_saves_stock_and_journal(): void
    {
        $entity = PurchaseRecordEntity::fromArray([
            'branch_uuid' => 'branch-123',
            'company_uuid' => 'company-123',
            'supplier_uuid' => 'supplier-123',
            'document_no' => 'PUR-001',
            'date' => (string) time(),
            'total_amount' => 5000,
            'actor_uuid' => 'actor-123',
            'items' => [
                ['product_uuid' => 'prod-123', 'unit_uuid' => 'unit-123', 'qty' => 5, 'price' => 1000],
            ],
        ]);
        $saved = PurchaseRecordEntity::fromArray([...$entity->toArray(), 'uuid' => 'purchase-123']);

        $this->repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PurchaseRecordEntity::class))
            ->andReturn($saved);
        $this->stockService->shouldReceive('saveMovement')->once();
        $this->journalService->shouldReceive('UpsertJournal')
            ->once()
            ->andReturn(JournalEntity::fromArray([
                'branch_uuid' => 'branch-123',
                'reference_no' => 'PUR-001',
                'reference_uuid' => 'purchase-123',
                'journal_date' => time(),
                'lines' => [],
            ]));

        $result = $this->service->CreatePurchase($entity);
        $this->assertSame('purchase-123', $result->uuid);
    }

    public function test_delete_calls_repository(): void
    {
        $this->repository->shouldReceive('delete')
            ->once()
            ->with('uuid-123', 'branch-123', 'company-123', 'actor-123');

        $this->service->delete('uuid-123', 'branch-123', 'company-123', 'actor-123');
        $this->assertTrue(true);
    }
}
