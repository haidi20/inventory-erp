<?php

namespace Tests\Feature\Flow;

use Tests\TestCase;
use App\Modules\Operational\Procurement\Services\PurchaseOrderService;
use App\Modules\Operational\Procurement\Services\PurchaseService;
use App\Modules\Operational\Inventory\Services\StockBalanceService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderItemEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseItemEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Core\Identity\Models\User;

class ProcurementToAccountingFlowTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderService $poService;
    private PurchaseService $purchaseService;
    private string $branchUuid;
    private string $companyUuid;
    private string $supplierUuid;
    private string $productUuid;
    private string $warehouseUuid;
    private string $actorUuid;
    private string $inventoryAccountUuid;
    private string $payableAccountUuid;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->poService = app(PurchaseOrderService::class);
        $this->purchaseService = app(PurchaseService::class);
        
        // Seed basis data
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = OFF') : DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->seedBasicData();
    }

    private function seedBasicData()
    {
        $this->branchUuid = (string) Str::uuid();
        $this->companyUuid = (string) Str::uuid();
        $this->supplierUuid = (string) Str::uuid();
        $this->productUuid = (string) Str::uuid();
        $this->warehouseUuid = (string) Str::uuid();
        // User sungguhan: stock_movements.user_uuid punya FK ke users, sehingga
        // actor_uuid fabricate acak akan gagal FK.
        $this->actorUuid = User::factory()->create()->uuid;
        $this->inventoryAccountUuid = (string) Str::uuid();
        $this->payableAccountUuid = (string) Str::uuid();

        DB::table('branches')->insert(['uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'name' => 'Main Branch', 'short_name' => 'MB', 'created_at' => time()]);
        DB::table('suppliers')->insert(['uuid' => $this->supplierUuid, 'branch_uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'name' => 'Mock Supplier', 'created_at' => time()]);
        DB::table('locations')->insert(['uuid' => $this->warehouseUuid, 'branch_uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'code' => 'W01', 'name' => 'W1', 'created_at' => time()]);
        DB::table('items')->insert([
            'uuid' => $this->productUuid, 
            'branch_uuid' => $this->branchUuid, 
            'company_uuid' => $this->companyUuid, 
            'code' => 'P01', 
            'name' => 'Medicine', 
            'created_at' => time()
        ]);
        
        // Seed COA for accounting
        DB::table('accounts')->insert([
            ['uuid' => $this->inventoryAccountUuid, 'branch_uuid' => $this->branchUuid, 'code' => '1101', 'name' => 'Persediaan Barang', 'type' => 'asset', 'created_at' => time()],
            ['uuid' => $this->payableAccountUuid, 'branch_uuid' => $this->branchUuid, 'code' => '2101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'created_at' => time()],
        ]);

        DB::table('closing_periods')->insert([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branchUuid,
            'period' => date('Y-m'),
            'name' => 'Current Period',
            'status' => 'open',
            'start_date' => strtotime(date('Y-m-01')),
            'end_date' => strtotime(date('Y-m-t')),
            'created_at' => time(),
        ]);
    }

    /** @test */
    public function it_completes_procurement_cycle_from_po_to_journal()
    {
        // 1. Create PO
        $poItems = [
            new PurchaseOrderItemEntity(product_uuid: $this->productUuid, unit_uuid: (string) Str::uuid(), quantity: 10, price: 5000)
        ];
        $poEntity = new PurchaseOrderEntity(
            branch_uuid: $this->branchUuid,
            company_uuid: $this->companyUuid,
            supplier_uuid: $this->supplierUuid,
            location_uuid: $this->warehouseUuid,
            document_no: 'PO-2026-001',
            date: time(),
            items: $poItems
        );

        $po = $this->poService->CreateOrUpdate($poEntity);
        $this->assertNotNull($po);
        $this->assertDatabaseHas('purchase_orders', ['document_no' => 'PO-2026-001']);

        // 2. Receive Purchase (Invoice) based on PO
        // Assume PurchaseService has a method to create purchase from PO
        // Note: For now we simulate the creation
        $purchaseEntity = new PurchaseRecordEntity(
            branch_uuid: $this->branchUuid,
            company_uuid: $this->companyUuid,
            supplier_uuid: $this->supplierUuid,
            purchase_order_uuid: $po->uuid,
            location_uuid: $this->warehouseUuid,
            document_no: 'INV-2026-001',
            date: (string) time(),
            total_amount: 50000,
            items: [
                new PurchaseItemEntity(
                    product_uuid: $this->productUuid,
                    qty: 10,
                    price: 5000,
                    subtotal: 50000,
                    unit_uuid: (string) Str::uuid(),
                ),
            ],
            actor_uuid: $this->actorUuid,
            description: json_encode([
                'inventory_account' => $this->inventoryAccountUuid,
                'payable_account' => $this->payableAccountUuid,
            ]),
        );

        $purchase = $this->purchaseService->CreatePurchase($purchaseEntity);
        $this->assertNotNull($purchase);

        // 3. Check Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'product_uuid' => $this->productUuid,
            'type' => 'IN'
        ]);

        // 4. Check Journal Entry
        $this->assertDatabaseHas('journals', [
            'reference_no' => 'INV-2026-001'
        ]);
    }
}
