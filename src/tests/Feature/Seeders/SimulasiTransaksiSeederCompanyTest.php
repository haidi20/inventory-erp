<?php

namespace Tests\Feature\Seeders;

use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Inventory\Models\StockMovement;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Database\Seeders\V1\SimulasiTransaksiSeeder;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulasiTransaksiSeederCompanyTest extends TestCase
{
    public function test_simulasi_transaksi_seeder_keeps_transactions_inside_sandbox_company(): void
    {
        $this->seed(RuntimeCompanySeeder::class);

        $productionBranch = Branch::query()
            ->where('short_name', RuntimeCompanySeeder::PRODUCTION_BRANCH_SHORT_NAME)
            ->firstOrFail();

        Patient::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'medical_record_no' => 'MR-PROD-SIM-001',
            'nik' => '7301' . str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
            'name' => 'Production Patient Simulasi',
            'birth_date' => now()->subYears(30)->timestamp,
            'gender' => 'L',
            'is_active' => true,
        ]);

        Supplier::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'name' => 'Production Supplier Simulasi',
            'is_active' => true,
        ]);

        Product::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'code' => 'PROD-SIM-001',
            'name' => 'Production Product Simulasi',
            'base_price_buy' => 10000,
            'base_price_sell' => 15000,
            'price_buy' => 10000,
            'price_sell' => 15000,
            'is_active' => true,
        ]);

        Service::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'code' => 'SV-PROD-SIM-001',
            'name' => 'Production Service Simulasi',
            'duration_minutes' => 15,
            'price' => 50000,
            'doctor_fee' => 0,
            'is_active' => true,
        ]);

        Employee::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'employee_no' => 'DR-PROD-SIM-001',
            'name' => 'Production Doctor Simulasi',
            'role' => 'dokter',
            'position' => 'Dokter',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $productionBranch->uuid,
            'company_uuid' => $productionBranch->company_uuid,
            'employee_no' => 'KS-PROD-SIM-001',
            'name' => 'Production Cashier Simulasi',
            'role' => 'kasir',
            'position' => 'Kasir',
            'is_active' => true,
        ]);

        $this->seed(DemoSeeder::class);
        $this->seed(SimulasiTransaksiSeeder::class);

        $sandboxBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $purchase = Purchase::query()->where('invoice_no', 'PO-SIM-001')->firstOrFail();
        $registration = Registration::query()->where('queue_no', 'A-001')->firstOrFail();
        $encounter = Encounter::query()->where('registration_uuid', $registration->uuid)->firstOrFail();
        $sale = Sale::query()->where('sale_no', 'INV-SIM-001')->firstOrFail();

        $this->assertSame($sandboxBranch->uuid, $purchase->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $purchase->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $registration->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $registration->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $encounter->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $encounter->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $sale->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $sale->company_uuid);

        $payable = Payable::query()
            ->where('reference_type', 'purchase')
            ->where('reference_uuid', $purchase->uuid)
            ->firstOrFail();

        $receivable = Receivable::query()
            ->where('reference_type', 'sale')
            ->where('reference_uuid', $sale->uuid)
            ->firstOrFail();

        $stockIn = StockMovement::query()
            ->where('reference_type', 'purchase')
            ->where('reference_uuid', $purchase->uuid)
            ->firstOrFail();

        $stockOut = StockMovement::query()
            ->where('reference_type', 'sale')
            ->where('reference_uuid', $sale->uuid)
            ->firstOrFail();

        $this->assertSame($sandboxBranch->uuid, $payable->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $payable->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $receivable->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $receivable->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $stockIn->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $stockIn->company_uuid);
        $this->assertSame($sandboxBranch->uuid, $stockOut->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $stockOut->company_uuid);
    }
}
