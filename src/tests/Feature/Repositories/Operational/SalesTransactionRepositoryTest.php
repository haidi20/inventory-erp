<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Sales\Entities\SalesOrderEntity;
use App\Modules\Operational\Sales\Entities\DeliveryOrderEntity;
use App\Modules\Operational\Sales\Entities\SalesInvoiceEntity;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Modules\Finance\Ledger\Models\SalesInvoice;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected SalesTransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SalesTransactionRepository();
    }

    // --- Sales Order Tests ---

    public function test_it_can_store_sales_order()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();

        $entity = new SalesOrderEntity(
            branch_uuid: $branch->uuid,
            company_uuid: $company->uuid,
            person_uuid: (string) Str::uuid(),
            location_uuid: (string) Str::uuid(),
            payment_term_uuid: (string) Str::uuid(),
            sales_order_no: 'SO-TEST-001',
            sales_order_date: time(),
            status: 'Open',
            details: [['item_uuid' => (string) Str::uuid(), 'qty' => 10, 'price' => 1000, 'total' => 10000]],
            actor_uuid: (string) Str::uuid()
        );

        $so = $this->repository->storeSO($entity);

        $this->assertDatabaseHas('sales_orders', ['uuid' => $so->uuid, 'sales_order_no' => 'SO-TEST-001']);
        $this->assertCount(1, $so->details);
    }

    public function test_it_can_get_so_by_order_no()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();
        $so = SalesOrder::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'sales_order_no' => 'SO-UNIQUE-123'
        ]);

        $found = $this->repository->getSOByOrderNo('SO-UNIQUE-123', $branch->uuid, $company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($so->uuid, $found->uuid);
    }

    public function test_it_can_get_page_data_so_with_filters()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();

        SalesOrder::factory()->create(['branch_uuid' => $branch->uuid, 'company_uuid' => $company->uuid, 'sales_order_no' => 'TARGET', 'sales_order_date' => strtotime('2026-04-01')]);
        SalesOrder::factory()->create(['branch_uuid' => $branch->uuid, 'company_uuid' => $company->uuid, 'sales_order_no' => 'OTHER', 'sales_order_date' => strtotime('2026-03-01')]);

        $entity = new SalesOrderEntity(
            branch_uuid: $branch->uuid,
            company_uuid: $company->uuid,
            start_date: (string) strtotime('2026-03-15'),
            search: 'TARGET'
        );

        $result = $this->repository->getPageDataSO($entity);

        $this->assertEquals(1, $result['total']);
    }

    // --- Delivery Order Tests ---

    public function test_it_can_store_delivery_order()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();

        $entity = new DeliveryOrderEntity(
            branch_uuid: $branch->uuid,
            company_uuid: $company->uuid,
            person_uuid: (string) Str::uuid(),
            location_uuid: (string) Str::uuid(),
            delivery_no: 'DO-TEST-001',
            delivery_date: time(),
            details: [['item_uuid' => (string) Str::uuid(), 'qty' => 5]],
            actor_uuid: (string) Str::uuid()
        );

        $do = $this->repository->storeDO($entity);

        $this->assertDatabaseHas('delivery_orders', ['uuid' => $do->uuid, 'delivery_no' => 'DO-TEST-001']);
    }

    public function test_it_can_get_do_by_delivery_no()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();
        $do = DeliveryOrder::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'delivery_no' => 'DO-UNIQUE-123'
        ]);

        $found = $this->repository->getDOByDeliveryNo('DO-UNIQUE-123', $branch->uuid, $company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($do->uuid, $found->uuid);
    }

    // --- Sales Invoice Tests ---

    public function test_it_can_store_sales_invoice()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();

        $entity = new SalesInvoiceEntity(
            branch_uuid: $branch->uuid,
            company_uuid: $company->uuid,
            person_uuid: (string) Str::uuid(),
            invoice_no: 'INV-TEST-001',
            invoice_date: time(),
            payment_term_uuid: (string) Str::uuid(),
            location_uuid: (string) Str::uuid(),
            status: 'Posted',
            details: [['item_uuid' => (string) Str::uuid(), 'qty' => 2, 'price' => 5000, 'total' => 10000]],
            actor_uuid: (string) Str::uuid()
        );

        $inv = $this->repository->storeInvoice($entity);

        $this->assertDatabaseHas('sales_invoices', ['uuid' => $inv->uuid, 'invoice_no' => 'INV-TEST-001']);
    }

    public function test_it_can_get_invoice_by_no()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();
        $inv = SalesInvoice::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'invoice_no' => 'INV-UNIQUE-123'
        ]);

        $found = $this->repository->getInvoiceByInvoiceNo('INV-UNIQUE-123', $branch->uuid, $company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($inv->uuid, $found->uuid);
    }

    // --- Aging & Helpers ---

    public function test_it_can_store_and_get_aging()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();
        $invoice = SalesInvoice::factory()->create(['branch_uuid' => $branch->uuid, 'company_uuid' => $company->uuid]);

        $agingData = [
            'uuid' => (string) Str::uuid(),
            'sales_invoice_uuid' => $invoice->uuid,
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'due_date' => time() + 86400,
            'amount' => 5000,
            'remaining' => 5000,
            'created_by' => 'tester'
        ];

        $this->repository->storeAging($agingData);

        $agings = $this->repository->getAgingByInvoiceUuid($invoice->uuid, $branch->uuid, $company->uuid);
        
        $this->assertCount(1, $agings);
        $this->assertEquals(5000, $agings[0]['amount']);
    }

    public function test_it_can_find_data_for_export()
    {
        $branch = Branch::factory()->create();
        $company = Company::factory()->create();
        SalesOrder::factory()->count(2)->create(['branch_uuid' => $branch->uuid, 'company_uuid' => $company->uuid]);

        $entity = new SalesOrderEntity(branch_uuid: $branch->uuid, company_uuid: $company->uuid);
        $results = $this->repository->findSOForExport($entity);

        $this->assertCount(2, $results);
    }
}
