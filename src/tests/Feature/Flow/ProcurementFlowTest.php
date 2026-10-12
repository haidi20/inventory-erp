<?php

namespace Tests\Feature\Flow;

use Tests\TestCase;
use App\Modules\Operational\Procurement\Services\PurchaseOrderService;
use App\Modules\Operational\Procurement\Services\PurchaseService;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderItemEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseItemEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Core\Identity\Models\User;

class ProcurementFlowTest extends TestCase
{
    private PurchaseOrderService $poService;
    private PurchaseService $purchaseService;
    private string $branchUuid;
    private string $companyUuid;
    private string $supplierUuid;
    private string $warehouseUuid;
    private string $productUuid;
    private string $actorUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poService = app(PurchaseOrderService::class);
        $this->purchaseService = app(PurchaseService::class);
        
        $this->seedDatabase();
    }

    private function seedDatabase()
    {
        $this->branchUuid = (string) Str::uuid();
        $this->companyUuid = (string) Str::uuid();
        $this->supplierUuid = (string) Str::uuid();
        $this->warehouseUuid = (string) Str::uuid();
        $this->productUuid = (string) Str::uuid();
        // User sungguhan: stock_movements.user_uuid punya FK ke users, sehingga
        // actor_uuid fabricate acak akan gagal FK.
        $this->actorUuid = User::factory()->create()->uuid;
        $this->inventoryAccountUuid = (string) Str::uuid();
        $this->payableAccountUuid = (string) Str::uuid();

        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = OFF') : DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('branches')->insert(['uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'name' => 'Main', 'short_name' => 'MB', 'created_at' => time()]);
        DB::table('suppliers')->insert(['uuid' => $this->supplierUuid, 'name' => 'Supplier A', 'branch_uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'created_at' => time()]);
        DB::table('locations')->insert(['uuid' => $this->warehouseUuid, 'name' => 'Gudang Utama', 'branch_uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'code' => 'W01', 'created_at' => time()]);
        DB::table('items')->insert(['uuid' => $this->productUuid, 'name' => 'Paracetamol', 'branch_uuid' => $this->branchUuid, 'company_uuid' => $this->companyUuid, 'code' => 'P01', 'created_at' => time()]);
        
        // Seed accounts
        DB::table('accounts')->insert([
            ['uuid' => $this->inventoryAccountUuid, 'branch_uuid' => $this->branchUuid, 'code' => '1101', 'name' => 'Inventory', 'type' => 'asset', 'created_at' => time()],
            ['uuid' => $this->payableAccountUuid, 'branch_uuid' => $this->branchUuid, 'code' => '2101', 'name' => 'Accounts Payable', 'type' => 'liability', 'created_at' => time()],
        ]);
        
        // Seed active accounting period matching v1 schema
        DB::table('closing_periods')->insert([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branchUuid,
            'period' => date('Y-m'),
            'name' => 'Current Period',
            'status' => 'open',
            'start_date' => strtotime(date('Y-m-01')),
            'end_date' => strtotime(date('Y-m-t')),
            'created_at' => time()
        ]);
        
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = ON') : DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /** @test */
    public function it_completes_full_procurement_flow()
    {
        // 1. Create Purchase Order
        $poItems = [
            new PurchaseOrderItemEntity(product_uuid: $this->productUuid, unit_uuid: (string) Str::uuid(), quantity: 10, price: 5000)
        ];
        $poEntity = new PurchaseOrderEntity(
            branch_uuid: $this->branchUuid,
            company_uuid: $this->companyUuid,
            supplier_uuid: $this->supplierUuid,
            location_uuid: $this->warehouseUuid,
            document_no: 'PO-FLOW-001',
            date: time(),
            items: $poItems
        );

        $po = $this->poService->CreateOrUpdate($poEntity);
        $this->assertDatabaseHas('purchase_orders', ['uuid' => $po->uuid]);

        // 2. Create Purchase (Penerimaan Barang) based on PO
        $pItems = [
            new PurchaseItemEntity(product_uuid: $this->productUuid, qty: 10, price: 5000, subtotal: 50000, unit_uuid: (string) Str::uuid())
        ];
        $purchaseEntity = new PurchaseRecordEntity(
            branch_uuid: $this->branchUuid,
            company_uuid: $this->companyUuid,
            supplier_uuid: $this->supplierUuid,
            purchase_order_uuid: $po->uuid,
            location_uuid: $this->warehouseUuid,
            document_no: 'INV-FLOW-001',
            date: time(),
            total_amount: 50000,
            items: $pItems,
            actor_uuid: $this->actorUuid,
            description: json_encode([
                'inventory_account' => $this->inventoryAccountUuid,
                'payable_account' => $this->payableAccountUuid
            ])
        );

        // This triggers Stock and Journal
        $purchase = $this->purchaseService->CreatePurchase($purchaseEntity);
        $this->assertNotNull($purchase);

        // 3. Verify Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'product_uuid' => $this->productUuid,
            'type' => 'IN'
        ]);

        // 4. Verify Journal Entry
        $this->assertDatabaseHas('journals', [
            'reference_no' => 'INV-FLOW-001'
        ]);
    }
}
