<?php

namespace Tests\Unit\Services\Operational;

use App\Modules\Operational\Sales\Entities\SalesOrderEntity;
use App\Modules\Operational\Sales\Entities\DeliveryOrderEntity;
use App\Modules\Operational\Sales\Entities\SalesInvoiceEntity;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepositoryInterface;
use App\Modules\Operational\Sales\Services\SalesTransactionService;
use Mockery;
use Tests\TestCase;

class SalesTransactionServiceTest extends TestCase
{
    protected $repository;
    protected SalesTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(SalesTransactionRepositoryInterface::class);
        $this->service = new SalesTransactionService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- Sales Order Tests ---

    public function test_store_so_validates_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entity = new SalesOrderEntity(sales_order_no: '');
        $this->service->storeSO($entity);
    }

    public function test_store_so_calls_repository_with_valid_data()
    {
        $entity = new SalesOrderEntity(
            branch_uuid: 'B001',
            person_uuid: 'P001',
            location_uuid: 'W001',
            payment_term_uuid: 'PT01',
            sales_order_no: 'SO-001',
            sales_order_date: time()
        );
        $so = new SalesOrderEntity(uuid: 'SO-UUID', branch_uuid: 'B001', company_uuid: 'C001');

        $this->repository->shouldReceive('storeSO')
            ->once()
            ->with($entity)
            ->andReturn($so);

        $result = $this->service->storeSO($entity);
        $this->assertSame($so, $result);
    }

    // --- Delivery Order Tests ---

    public function test_store_do_validates_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entity = new DeliveryOrderEntity(delivery_no: 'DO-001'); // Missing branch, etc.
        $this->service->storeDO($entity);
    }

    public function test_store_do_calls_repository_with_valid_data()
    {
        $entity = new DeliveryOrderEntity(
            branch_uuid: 'B001',
            location_uuid: 'W001',
            person_uuid: 'P001',
            delivery_no: 'DO-001',
            details: [['item_uuid' => 'ITM01', 'qty' => 5]]
        );
        $do = new DeliveryOrderEntity(uuid: 'DO-UUID', branch_uuid: 'B001', company_uuid: 'C001');

        $this->repository->shouldReceive('storeDO')
            ->once()
            ->with($entity)
            ->andReturn($do);

        $result = $this->service->storeDO($entity);
        $this->assertSame($do, $result);
    }

    // --- Sales Invoice Tests ---

    public function test_store_invoice_handles_agings()
    {
        $entity = new SalesInvoiceEntity(
            branch_uuid: 'B001',
            invoice_no: 'INV-001',
            agings: [['due_date' => time(), 'amount' => 1000]]
        );
        
        $inv = new SalesInvoiceEntity(uuid: 'INV-UUID', branch_uuid: 'B001', company_uuid: 'C001');

        $this->repository->shouldReceive('storeInvoice')
            ->once()
            ->with($entity)
            ->andReturn($inv);

        $result = $this->service->storeInvoice($entity);
        $this->assertSame($inv, $result);
    }

    public function test_get_invoice_with_aging_returns_combined_data()
    {
        $inv = new SalesInvoiceEntity(uuid: 'UUID', branch_uuid: 'BR', company_uuid: 'CO');
        $agings = [['amount' => 500]];

        $this->repository->shouldReceive('getInvoiceByUuid')->once()->andReturn($inv);
        $this->repository->shouldReceive('getAgingByInvoiceUuid')->once()->andReturn($agings);

        $result = $this->service->getInvoiceWithAging('UUID', 'BR', 'CO');
        
        $this->assertEquals($inv, $result['invoice']);
        $this->assertEquals($agings, $result['agings']);
    }

    public function test_export_so_calls_repository()
    {
        $entity = new SalesOrderEntity();
        $this->repository->shouldReceive('findSOForExport')->once()->andReturn([]);

        $result = $this->service->exportSO($entity);

        $this->assertSame([], $result);
    }
}
