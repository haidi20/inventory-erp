<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepository;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderListResultEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderRepository $repository;
    private string $branchUuid;
    private string $supplierUuid;
    private string $warehouseUuid;
    private string $companyUuid;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branchUuid = (string) Str::uuid();
        $this->supplierUuid = (string) Str::uuid();
        $this->warehouseUuid = (string) Str::uuid();
        $this->companyUuid = (string) Str::uuid();

        // FULL SEEDING TO SATISFY FOREIGN KEYS
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = OFF') : DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('branches')->insert([
            'uuid' => $this->branchUuid,
            'name' => 'Test Branch',
            'short_name' => 'TBR',
            'created_at' => time()
        ]);

        DB::table('suppliers')->insert([
            'uuid' => $this->supplierUuid,
            'name' => 'Test Supplier',
            'branch_uuid' => $this->branchUuid,
            'created_at' => time()
        ]);

        DB::table('locations')->insert([
            'uuid' => $this->warehouseUuid,
            'name' => 'Test Warehouse',
            'code' => 'WH01',
            'branch_uuid' => $this->branchUuid,
            'company_uuid' => $this->companyUuid,
            'created_at' => time()
        ]);

        $this->repository = new PurchaseOrderRepository();
    }

    /** @test */
    public function it_can_save_a_purchase_order()
    {
        $poUuid = (string) Str::uuid();

        $entity = new PurchaseOrderEntity(
            uuid: $poUuid,
            branch_uuid: $this->branchUuid,
            company_uuid: $this->companyUuid,
            supplier_uuid: $this->supplierUuid,
            location_uuid: $this->warehouseUuid,
            document_no: 'PO-TEST-999',
            date: time(),
            items: []
        );

        $result = $this->repository->save($entity);

        $this->assertNotNull($result);
        $this->assertEquals('PO-TEST-999', $result->document_no);
        $this->assertDatabaseHas('purchase_orders', ['uuid' => $poUuid]);
    }

    /** @test */
    public function it_can_find_all_as_result_entity()
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->repository->save(new PurchaseOrderEntity(
                uuid: (string) Str::uuid(),
                branch_uuid: $this->branchUuid,
                company_uuid: $this->companyUuid,
                supplier_uuid: $this->supplierUuid,
                location_uuid: $this->warehouseUuid,
                document_no: "PO-FIND-$i",
                date: time(),
                items: []
            ));
        }

        $filter = new PurchaseOrderFilterEntity(branch_uuid: $this->branchUuid, per_page: 2);
        $result = $this->repository->findAll($filter);

        $this->assertInstanceOf(PurchaseOrderListResultEntity::class, $result);
        $this->assertGreaterThan(0, $result->total);
        $this->assertCount(2, $result->items);
    }
}
