<?php

namespace Tests\Unit\Services\Operational;

use Tests\TestCase;
use App\Modules\Operational\Inventory\Services\StockBalanceService;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepositoryInterface;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentItemEntity;
use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockReportResultEntity;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class StockBalanceServiceTest extends TestCase
{
    private StockBalanceService $service;
    private MockInterface $stockRepo;
    private MockInterface $adjRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockRepo = Mockery::mock(StockRepositoryInterface::class);
        $this->adjRepo = Mockery::mock(StockAdjustmentRepositoryInterface::class);
        $this->service = new StockBalanceService($this->stockRepo, $this->adjRepo);
    }

    #[Test]
    public function it_can_get_stock_balances()
    {
        $filter = new StockFilterEntity(page: 1, per_page: 10);
        $mockResult = new StockReportResultEntity(items: [], total: 0, per_page: 10, current_page: 1);

        $this->stockRepo->shouldReceive('getStockBalanceReport')
            ->once()
            ->with($filter)
            ->andReturn($mockResult);

        $result = $this->service->GetBalances($filter);
        $this->assertEquals(0, $result->total);
    }

    #[Test]
    public function it_can_create_a_stock_adjustment()
    {
        $items = [
            new StockAdjustmentItemEntity(
                product_uuid: (string) Str::uuid(),
                measurement_uuid: (string) Str::uuid(),
                adjustment_qty: 5
            )
        ];

        $entity = new StockAdjustmentEntity(
            location_uuid: (string) Str::uuid(),
            branch_uuid: (string) Str::uuid(),
            company_uuid: (string) Str::uuid(),
            date: time(),
            items: $items,
            actor_uuid: (string) Str::uuid()
        );

        $this->adjRepo->shouldReceive('storeAdjustment')
            ->once()
            ->with(Mockery::type(StockAdjustmentEntity::class))
            ->andReturn(new StockAdjustmentEntity(uuid: 'adj-123'));

        $result = $this->service->CreateAdjustment($entity);
        $this->assertEquals('adj-123', $result->uuid);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
