<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Sales\Models\Sale;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class DemoSeederIsolationTest extends TestCase
{
    public function test_demo_seeder_keeps_demo_transactions_inside_sandbox_company(): void
    {
        $this->seed(RuntimeCompanySeeder::class);

        $productionBranch = Branch::query()
            ->where('short_name', RuntimeCompanySeeder::PRODUCTION_BRANCH_SHORT_NAME)
            ->firstOrFail();

        Patient::factory()->create([
            'medical_record_no' => 'RM-20240001',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
        ]);

        Service::query()->create([
            'uuid' => '90000000-0000-4000-8000-000000000001',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'code' => 'SV-001',
            'name' => 'Production Service',
            'duration_minutes' => 10,
            'price' => 99999,
            'doctor_fee' => 0,
            'is_active' => true,
        ]);

        Product::query()->create([
            'uuid' => '90000000-0000-4000-8000-000000000002',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'code' => 'AMX-500',
            'name' => 'Production Product',
            'base_price_buy' => 9999,
            'base_price_sell' => 19999,
            'is_active' => true,
        ]);

        Supplier::query()->create([
            'uuid' => '90000000-0000-4000-8000-000000000003',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'name' => 'AAA Production Supplier',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'uuid' => '90000000-0000-4000-8000-000000000004',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'name' => 'Production Doctor',
            'role' => 'dokter',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'uuid' => '90000000-0000-4000-8000-000000000005',
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'name' => 'Production Cashier',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->seed(DemoSeeder::class);

        $sandboxBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $sale = Sale::query()->where('sale_no', 'INV-POS-20260303-213032-921')->firstOrFail();
        $purchase = Purchase::query()->where('invoice_no', 'PO-227D43D')->firstOrFail();

        $this->assertSame($sandboxBranch->uuid, $sale->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $sale->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $purchase->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $purchase->company_uuid);

        $salePatient = Patient::query()->findOrFail($sale->patient_uuid);
        $purchaseSupplier = Supplier::query()->findOrFail($purchase->supplier_uuid);

        $this->assertSame($sandboxBranch->uuid, $salePatient->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $salePatient->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $purchaseSupplier->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $purchaseSupplier->company_uuid);
        $this->assertNotSame($productionBranch->uuid, $purchaseSupplier->branch_uuid);
    }
}
