<?php

namespace App\Modules\Operational\Sales\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Operational\Sales\Entities\ExaminationEntity;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Clinical\Registration\Services\RegistrationPublicService;
use App\Modules\Operational\Sales\Entities\SaleFilterEntity;
use App\Modules\Operational\Sales\Entities\SaleItemEntity;
use App\Modules\Operational\Sales\Entities\SaleTransactionEntity;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Clinical\Registration\Models\Patient;
use RuntimeException;

class TransactionRepository
{
    public function __construct(
        private RegistrationPublicService $registrationService
    ) {}

    // -----------------------------------------------------------------------
    // Resolve company_uuid dari auth user
    // -----------------------------------------------------------------------

    private function companyUuid(): string
    {
        return (string) (auth()->user()?->company_uuid ?? '');
    }

    private function branchUuid(): string
    {
        return (string) (auth()->user()?->branch_uuid ?? '');
    }

    public function patientExists(string $uuid): bool
    {
        return Patient::query()
            ->where('uuid', $uuid)
            ->where('company_uuid', $this->companyUuid())
            ->exists();
    }

    public function employeeExists(string $uuid): bool
    {
        return Employee::query()
            ->where('uuid', $uuid)
            ->where('company_uuid', $this->companyUuid())
            ->exists();
    }

    public function productsExist(array $uuids): bool
    {
        $uuids = array_values(array_unique(array_filter($uuids)));

        if ($uuids === []) {
            return true;
        }

        return Product::query()
            ->whereIn('uuid', $uuids)
            ->where('company_uuid', $this->companyUuid())
            ->count() === count($uuids);
    }

    // -----------------------------------------------------------------------
    // getSalePageData â€” dropdown data untuk form POS
    // -----------------------------------------------------------------------

    public function getSalePageData(): array
    {
        $companyUuid = $this->companyUuid();

        $currentCashier = Employee::query()
            ->where('user_uuid', auth()->user()->uuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return [
            'patients' => $this->registrationService->getActivePatients($companyUuid),
            'products' => Product::query()
                ->where('company_uuid', $companyUuid)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'cashiers' => Employee::query()
                ->where('company_uuid', $companyUuid)
                ->where('is_active', true)
                ->where('role', 'kasir')
                ->orderBy('name')
                ->get(),
            'currentCashierUuid' => $currentCashier?->uuid,
            'suggestedSaleNo'    => $this->generateSaleInvoiceNo(),
        ];
    }

    // -----------------------------------------------------------------------
    // getSalesPageData â€” daftar penjualan scoped by company
    // -----------------------------------------------------------------------

    public function getSalesPageData(): array
    {
        $companyUuid = $this->companyUuid();

        $sales = Sale::query()
            ->where('company_uuid', $companyUuid)
            ->with(['patient', 'cashier'])
            ->orderByDesc('sale_at')
            ->paginate(20)
            ->withQueryString();

        return [
            'sales'  => $sales,
            'search' => '',
        ];
    }

    // -----------------------------------------------------------------------
    // getSalesFormData â€” alias getSalePageData
    // -----------------------------------------------------------------------

    public function getSalesFormData(mixed $filter = null): array
    {
        return $this->getSalePageData();
    }

    // -----------------------------------------------------------------------
    // getPurchasePageData â€” stub (data pembelian ada di PurchaseRepository)
    // -----------------------------------------------------------------------

    public function getPurchasePageData(mixed $filter = null): array
    {
        return [
            'purchases' => collect(),
            'note'      => 'Gunakan PurchaseRepository untuk data pembelian yang scoped by company.',
        ];
    }

    // -----------------------------------------------------------------------
    // getPayablePageData â€” stub (data hutang ada di accounting repository)
    // -----------------------------------------------------------------------

    public function getPayablePageData(mixed $filter = null): array
    {
        return [
            'payables' => collect(),
            'note'     => 'Gunakan AccountingRepository untuk data hutang yang scoped by company.',
        ];
    }

    // -----------------------------------------------------------------------
    // createSale â€” buat transaksi POS dengan company_uuid dari auth user
    // -----------------------------------------------------------------------

    public function createSale(SaleTransactionEntity $saleTransactionEntity): string
    {
        DB::beginTransaction();
        try {
            $companyUuid = $this->companyUuid();
            $branchUuid  = $this->branchUuid();

            $saleNo      = $this->generateSaleInvoiceNo();
            $cashierUuid = $saleTransactionEntity->cashier_uuid ?: Employee::query()
                ->where('user_uuid', auth()->user()->uuid)
                ->where('company_uuid', $companyUuid)
                ->value('uuid');

            $lineItems   = [];
            $totalAmount = 0;

            foreach ($saleTransactionEntity->items as $itemEntity) {
                if (!$itemEntity instanceof SaleItemEntity) {
                    continue;
                }

                $product = Product::query()
                    ->where('uuid', $itemEntity->product_uuid)
                    ->where('company_uuid', $companyUuid)
                    ->firstOrFail();

                // Stok dikelola via item_balances/item_stocks â€” tidak ada kolom stock langsung di items
                $price    = (float) ($product->price_sell ?? $product->base_price_sell ?? 0);
                $subtotal = $price * (int) $itemEntity->qty;
                $totalAmount += $subtotal;

                $lineItems[] = [
                    'product'  => $product,
                    'qty'      => (int) $itemEntity->qty,
                    'price'    => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $paid   = (float) ($saleTransactionEntity->paid ?? 0);
            $change = max(0, $paid - $totalAmount);
            $status = $paid >= $totalAmount ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $sale = Sale::query()->create([
                'sale_no'        => $saleNo,
                'branch_uuid'    => $branchUuid,
                'company_uuid'   => $companyUuid,
                'patient_uuid'   => $saleTransactionEntity->patient_uuid,
                'cashier_uuid'   => $cashierUuid,
                'total_amount'   => $totalAmount,
                'paid'           => $paid,
                'change'         => $change,
                'payment_status' => $status,
                'sale_at'        => time(),
            ]);

            foreach ($lineItems as $lineItem) {
                $sale->items()->create([
                    'branch_uuid'  => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'item_type'    => 'product',
                    'item_uuid'    => $lineItem['product']->uuid,
                    'qty'          => $lineItem['qty'],
                    'price'        => $lineItem['price'],
                    'subtotal'     => $lineItem['subtotal'],
                ]);

                StockMovement::query()->create([
                    'branch_uuid'    => $branchUuid,
                    'company_uuid'   => $companyUuid,
                    'product_uuid'   => $lineItem['product']->uuid,
                    'qty'            => $lineItem['qty'] * -1,
                    'type'           => 'out',
                    'reference_type' => 'sale',
                    'reference_uuid' => $sale->uuid,
                ]);

                // Update physical stock balance in item_stocks
                ProductLocation::query()
                    ->where('item_uuid', $lineItem['product']->uuid)
                    ->where('branch_uuid', $branchUuid)
                    ->decrement('on_hand_qty', $lineItem['qty']);
            }

            if ($status !== 'paid') {
                Receivable::query()->updateOrCreate(
                    [
                        'branch_uuid'    => $branchUuid,
                        'reference_type' => 'sale',
                        'reference_uuid' => $sale->uuid,
                    ],
                    [
                        'company_uuid' => $companyUuid,
                        'amount'       => $totalAmount,
                        'paid'         => $paid,
                        'status'       => $status,
                        'due_date'     => date('Y-m-d', strtotime('+7 days')),
                    ]
                );
            }

            DB::commit();
            return $sale->uuid;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------------------
    // getSaleDetail â€” hanya tampilkan sale milik company yang sama
    // -----------------------------------------------------------------------

    public function getSaleDetail(string $uuid): Sale
    {
        return Sale::query()
            ->where('company_uuid', $this->companyUuid())
            ->with(['patient', 'cashier', 'items.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    // -----------------------------------------------------------------------
    // getReceivablePageData â€” scoped by company
    // -----------------------------------------------------------------------

    public function getReceivablePageData(mixed $search = null, mixed $status = null): array
    {
        $companyUuid = $this->companyUuid();

        $query = Receivable::query()
            ->where('company_uuid', $companyUuid)
            ->with(['sale.patient'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('sale', function ($builder) use ($search) {
                $builder->where('sale_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('patient', fn ($patient) => $patient->where('name', 'LIKE', "%{$search}%"));
            });
        }

        return [
            'receivables'    => $query->paginate(10)->withQueryString(),
            'totalOutstanding' => Receivable::query()
                ->where('company_uuid', $companyUuid)
                ->where('status', '!=', 'paid')
                ->get()
                ->sum(fn ($r) => $r->amount - $r->paid),
            'totalSettled'   => Receivable::query()
                ->where('company_uuid', $companyUuid)
                ->where('status', 'paid')
                ->sum('amount'),
        ];
    }

    // -----------------------------------------------------------------------
    // getExaminationPageData â€” scoped by company (via branch_uuid)
    // -----------------------------------------------------------------------

    public function getExaminationPageData(mixed $filter = null): array
    {
        $search      = is_string($filter) ? $filter : (is_array($filter) ? ($filter['search'] ?? '') : '');
        $status      = is_array($filter) ? ($filter['status'] ?? '') : '';
        $companyUuid = $this->companyUuid();

        $query = Registration::query()
            ->where('company_uuid', $companyUuid)
            ->with(['patient', 'doctor', 'encounter'])
            ->latest();

        if ($search) {
            $query->whereHas('patient', fn ($q) => $q->where('name', 'LIKE', "%{$search}%"));
        }

        if ($status) {
            $query->where('status', $status);
        }

        return [
            'registrations' => $query->paginate(20)->withQueryString(),
        ];
    }

    // -----------------------------------------------------------------------
    // getExaminationFormData
    // -----------------------------------------------------------------------

    public function getExaminationFormData(string $registrationUuid): array
    {
        $registration = Registration::query()
            ->where('uuid', $registrationUuid)
            ->where('company_uuid', $this->companyUuid())
            ->with(['patient', 'doctor', 'encounter.items.service'])
            ->firstOrFail();

        return [
            'registration' => $registration,
            'services'     => Service::query()
                ->where('company_uuid', $this->companyUuid())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    // -----------------------------------------------------------------------
    // storeExamination
    // -----------------------------------------------------------------------

    public function storeExamination(ExaminationEntity $entity): void
    {
        $branchUuid  = $this->branchUuid();
        $companyUuid = $this->companyUuid();

        DB::beginTransaction();
        try {
            $registration = Registration::query()
                ->where('uuid', $entity->registration_uuid)
                ->where('company_uuid', $companyUuid)
                ->firstOrFail();

            $encounter = Encounter::updateOrCreate(
                ['registration_uuid' => $entity->registration_uuid],
                [
                    'branch_uuid'  => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'patient_uuid' => $registration->patient_uuid,
                    'doctor_uuid'  => $entity->doctor_uuid,
                    'complaint'    => $entity->complaint,
                    'diagnosis'    => $entity->diagnosis,
                    'status'       => 'completed',
                ]
            );

            $encounter->items()->delete();
            foreach ($entity->services as $serviceUuid) {
                $service = Service::query()
                    ->where('uuid', $serviceUuid)
                    ->where('company_uuid', $companyUuid)
                    ->firstOrFail();

                $encounter->items()->create([
                    'branch_uuid'  => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'service_uuid' => $serviceUuid,
                    'price'        => $service->price,
                ]);
            }

            $registration->update(['status' => 'completed']);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------------------
    // Private: generate sale invoice number
    // -----------------------------------------------------------------------

    private function generateSaleInvoiceNo(): string
    {
        $datePart    = date('Ymd');
        $prefix      = "POS-{$datePart}-";
        $lastInvoice = Sale::query()
            ->where('sale_no', 'like', $prefix . '%')
            ->latest()
            ->value('sale_no');

        $sequence = 1;
        if ($lastInvoice && preg_match('/(\d{4})$/', $lastInvoice, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
