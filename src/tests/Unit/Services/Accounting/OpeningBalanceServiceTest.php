<?php

namespace Tests\Unit\Services\Accounting;

use Tests\TestCase;
use App\Modules\Finance\Ledger\Services\OpeningBalanceService;
use App\Modules\Finance\Ledger\Repositories\OpeningBalanceRepositoryInterface;
use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceFilterEntity;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;

class OpeningBalanceServiceTest extends TestCase
{
    private OpeningBalanceService $service;
    private MockInterface $repo;
    private MockInterface $inventoryService;
    private MockInterface $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(OpeningBalanceRepositoryInterface::class);
        $this->inventoryService = Mockery::mock(InventoryPublicService::class);
        $this->journalService = Mockery::mock(JournalService::class);
        $this->service = new OpeningBalanceService($this->repo, $this->inventoryService, $this->journalService);
    }

    /** @test */
    public function it_can_get_headers()
    {
        $filter = new OpeningBalanceFilterEntity(branch_uuid: (string) Str::uuid());
        $this->repo->shouldReceive('getOpeningBalanceHeaders')->once()->with($filter)->andReturn([]);
        $result = $this->service->GetOpeningBalanceHeaders($filter);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_can_create_a_header()
    {
        $entity = new OpeningBalanceEntity(
            branch_uuid: (string) Str::uuid(),
            transaction_no: 'OB-001',
            transaction_date: time()
        );

        $this->repo->shouldReceive('createOpeningBalanceHeader')
            ->once()
            ->with(Mockery::type(OpeningBalanceEntity::class))
            ->andReturn((object) ['uuid' => 'ob-uuid']);

        $result = $this->service->CreateHeader($entity);
        $this->assertEquals('ob-uuid', $result->uuid);
    }

    /** @test */
    public function it_fails_to_post_if_header_not_found()
    {
        $this->repo->shouldReceive('getOpeningBalanceHeader')->andReturn(null);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("header saldo awal tidak ditemukan");
        $this->service->PostOpeningBalance('invalid-uuid', 'user-uuid');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
