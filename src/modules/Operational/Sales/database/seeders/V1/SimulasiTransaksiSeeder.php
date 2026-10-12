<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulasiTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $branch = Branch::query()
                ->where('short_name', 'KDM')
                ->where('company_uuid', RuntimeCompanySeeder::SANDBOX_COMPANY_UUID)
                ->first();

            if (! $branch) {
                $this->command?->warn('Branch demo sandbox belum tersedia. Jalankan DemoSeeder terlebih dahulu.');
                return;
            }

            $branchUuid = $branch->uuid;
            $companyUuid = $branch->company_uuid;

            $suppliers = Supplier::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->get();
            $products  = Product::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->get();
            $services  = Service::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->get();
            $patients  = Patient::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->get();
            $doctor    = Employee::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->where('role', 'dokter')->first();
            $cashier   = Employee::query()->where('branch_uuid', $branchUuid)->where('company_uuid', $companyUuid)->where('role', 'kasir')->first();

            if ($suppliers->isEmpty() || $products->isEmpty() || $patients->isEmpty()) {
                $this->command->warn('Master data tidak lengkap. Jalankan DemoSeeder terlebih dahulu.');
                return;
            }

            // ── PEMBELIAN (purchases) ─────────────────────────────
            $purchaseData = [
                ['PO-SIM-001', 0, '2026-01-05', 3200000, 3200000, 'paid'],
                ['PO-SIM-002', 1, '2026-01-12', 1850000, 1850000, 'paid'],
                ['PO-SIM-003', 2, '2026-01-20', 2760000,       0, 'unpaid'],
                ['PO-SIM-004', 3, '2026-02-03', 980000,   980000, 'paid'],
                ['PO-SIM-005', 4, '2026-02-10', 4100000, 2000000, 'partial'],
                ['PO-SIM-006', 0, '2026-02-15', 1500000, 1500000, 'paid'],
                ['PO-SIM-007', 1, '2026-02-22', 3300000,       0, 'unpaid'],
                ['PO-SIM-008', 2, '2026-03-01', 2200000, 2200000, 'paid'],
                ['PO-SIM-009', 3, '2026-03-08', 1750000, 1750000, 'paid'],
                ['PO-SIM-010', 4, '2026-03-14', 5100000, 3000000, 'partial'],
            ];

            foreach ($purchaseData as [$no, $supIdx, $date, $total, $paid, $status]) {
                $sup = $suppliers[$supIdx % $suppliers->count()];
                $purchase = Purchase::updateOrCreate(
                    ['branch_uuid' => $branchUuid, 'invoice_no' => $no],
                    [
                        'company_uuid' => $companyUuid,
                        'supplier_uuid' => $sup->uuid,
                        'total' => $total,
                        'paid' => $paid,
                        'status' => $status,
                        'purchase_date' => strtotime($date),
                    ]
                );

                // Items
                $prod = $products[$supIdx % $products->count()];
                $qty  = rand(10, 50);
                PurchaseItem::updateOrCreate(
                    ['purchase_uuid' => $purchase->uuid, 'product_uuid' => $prod->uuid],
                    [
                        'branch_uuid' => $branchUuid,
                        'company_uuid' => $companyUuid,
                        'qty' => $qty,
                        'price' => $prod->price_buy,
                        'subtotal' => $qty * $prod->price_buy,
                    ]
                );

                // Payable
                Payable::updateOrCreate(
                    ['reference_type' => 'purchase', 'reference_uuid' => $purchase->uuid, 'branch_uuid' => $branchUuid],
                    [
                        'company_uuid' => $companyUuid,
                        'amount' => $total,
                        'paid' => $paid,
                        'status' => $status,
                        'due_date' => strtotime($date . ' +30 days'),
                    ]
                );

                // Stock movement
                StockMovement::updateOrCreate(
                    ['product_uuid' => $prod->uuid, 'reference_type' => 'purchase', 'reference_uuid' => $purchase->uuid, 'branch_uuid' => $branchUuid],
                    [
                        'company_uuid' => $companyUuid,
                        'qty' => $qty,
                        'type' => 'in',
                    ]
                );
            }

            // ── PEMERIKSAAN (encounters) ──────────────────────────
            $encounterData = [
                ['A-001', 0, 'Demam dan pusing', 'ISPA', '2026-01-08', 'done'],
                ['A-002', 1, 'Sakit perut mual', 'Gastritis', '2026-01-15', 'done'],
                ['A-003', 2, 'Batuk berdahak', 'Bronkitis', '2026-01-22', 'done'],
                ['A-004', 3, 'Nyeri kepala', 'Tension Headache', '2026-02-05', 'done'],
                ['A-005', 0, 'Demam tinggi', 'Tifoid', '2026-02-12', 'done'],
                ['A-006', 1, 'Gatal-gatal', 'Urtikaria', '2026-02-19', 'done'],
                ['A-007', 2, 'Check-up rutin', 'Sehat', '2026-03-03', 'done'],
                ['A-008', 3, 'Nyeri sendi', 'Artritis', '2026-03-10', 'done'],
                ['A-009', 0, 'Batuk pilek', 'ISPA', '2026-03-15', 'in_progress'],
                ['A-010', 1, 'Kontrol diabetes', 'DM Tipe 2', '2026-03-17', 'waiting'],
            ];

            foreach ($encounterData as [$qno, $patIdx, $complaint, $diagnosis, $date, $regStatus]) {
                $pat = $patients[$patIdx % $patients->count()];

                $reg = Registration::updateOrCreate(
                    ['branch_uuid' => $branchUuid, 'queue_no' => $qno, 'patient_uuid' => $pat->uuid],
                    [
                        'company_uuid' => $companyUuid,
                        'doctor_uuid' => $doctor?->uuid,
                        'payment_type' => 'mandiri',
                        'status' => $regStatus,
                        'registered_at' => strtotime($date),
                    ]
                );

                $encStatus = in_array($regStatus, ['done', 'in_progress']) ? 'posted' : 'draft';
                $encounter = Encounter::updateOrCreate(
                    ['branch_uuid' => $branchUuid, 'registration_uuid' => $reg->uuid],
                    [
                        'company_uuid' => $companyUuid,
                        'patient_uuid' => $pat->uuid,
                        'doctor_uuid' => $doctor?->uuid,
                        'complaint' => $complaint,
                        'diagnosis' => $diagnosis,
                        'status' => $encStatus,
                    ]
                );

                if ($encounter && $services->isNotEmpty()) {
                    $svc = $services->first();
                    EncounterItem::updateOrCreate(
                        ['encounter_uuid' => $encounter->uuid, 'item_type' => 'service', 'item_uuid' => $svc->uuid],
                        [
                            'branch_uuid' => $branchUuid,
                            'company_uuid' => $companyUuid,
                            'qty' => 1,
                            'price' => $svc->price,
                            'subtotal' => $svc->price,
                        ]
                    );
                }
            }

            // ── PENJUALAN / POS (sales) ───────────────────────────
            $salesData = [
                ['INV-SIM-001', 0, 60000,  60000,  0,     'paid',   '2026-01-10'],
                ['INV-SIM-002', 1, 35000,  35000,  0,     'paid',   '2026-01-18'],
                ['INV-SIM-003', 2, 90000,  90000,  0,     'paid',   '2026-01-25'],
                ['INV-SIM-004', 3, 45000,  45000,  0,     'paid',   '2026-02-07'],
                ['INV-SIM-005', 0, 120000, 120000, 0,     'paid',   '2026-02-14'],
                ['INV-SIM-006', 1, 75000,       0, 0,     'unpaid', '2026-02-20'],
                ['INV-SIM-007', 2, 200000, 100000, 0,     'partial','2026-03-05'],
                ['INV-SIM-008', 3, 55000,  55000,  5000,  'paid',   '2026-03-12'],
                ['INV-SIM-009', 0, 85000,  85000,  0,     'paid',   '2026-03-15'],
                ['INV-SIM-010', 1, 160000,      0, 0,     'unpaid', '2026-03-17'],
            ];

            foreach ($salesData as [$inv, $patIdx, $total, $paid, $change, $status, $date]) {
                $pat = $patients[$patIdx % $patients->count()];
                $sale = Sale::updateOrCreate(
                    ['branch_uuid' => $branchUuid, 'sale_no' => $inv],
                    [
                        'company_uuid' => $companyUuid,
                        'patient_uuid' => $pat->uuid,
                        'cashier_uuid' => $cashier?->uuid,
                        'total_amount' => $total,
                        'paid' => $paid,
                        'change' => $change,
                        'payment_status' => $status,
                        'payment_method' => 'cash',
                        'sale_at' => strtotime($date),
                        'created_at' => strtotime($date),
                        'updated_at' => strtotime($date),
                    ]
                );

                // Sale items
                $prod = $products[$patIdx % $products->count()];
                $qty  = (int) max(1, round($total / max(1, $prod->price_sell)));
                SaleItem::updateOrCreate(
                    ['sale_uuid' => $sale->uuid, 'item_type' => 'product', 'item_uuid' => $prod->uuid],
                    [
                        'branch_uuid' => $branchUuid,
                        'company_uuid' => $companyUuid,
                        'qty' => $qty,
                        'price' => $prod->price_sell,
                        'subtotal' => $total,
                    ]
                );

                // Stock out
                StockMovement::updateOrCreate(
                    ['product_uuid' => $prod->uuid, 'reference_type' => 'sale', 'reference_uuid' => $sale->uuid, 'branch_uuid' => $branchUuid],
                    [
                        'company_uuid' => $companyUuid,
                        'qty' => $qty,
                        'type' => 'out',
                    ]
                );

                // Receivable
                Receivable::updateOrCreate(
                    ['reference_type' => 'sale', 'reference_uuid' => $sale->uuid, 'branch_uuid' => $branchUuid],
                    [
                        'company_uuid' => $companyUuid,
                        'amount' => $total,
                        'paid' => $paid,
                        'status' => $status,
                        'due_date' => strtotime($date . ' +14 days'),
                    ]
                );
            }
        });
    }
}

