<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use Illuminate\Support\Facades\Hash;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Operational\Sales\Models\SaleItem;
use App\Modules\Operational\Sales\Models\Sale;
use Illuminate\Database\Seeder;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Master\Models\TaxSetting;
use App\Modules\Core\Identity\Models\User;

class DemoSeeder extends Seeder
{
    protected Branch $branch;
    protected array $accountMap = [];

    protected function companyUuid(): string
    {
        return $this->branch->company_uuid;
    }

    protected function findBranchScoped(string $modelClass, array $criteria)
    {
        return $modelClass::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('company_uuid', $this->companyUuid())
            ->where($criteria)
            ->first();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Core Infrastructure & Users
        $this->seedBranch();
        $this->seedUsersAndRoles();
        $this->seedEmployees();

        // 2. Master Data
        $this->seedPatients();
        $this->seedSuppliers();
        $this->seedProducts();
        $this->seedServices();
        $this->seedServicePackages();

        // 3. Accounting Foundation
        $this->seedChartOfAccounts();
        $this->seedAccountMappings();
        $this->seedOpeningBalances();

        // 4. Operational & Accounting Transactions (Demo Data)
        $this->seedDemoTransactions();
        $this->seedJournalEntries();
        $this->seedClosingPeriods();
    }

    protected function seedBranch(): void
    {
        $company = Company::updateOrCreate(
            ['uuid' => RuntimeCompanySeeder::SANDBOX_COMPANY_UUID],
            [
                'name' => 'Klinik Sandbox Testing',
                'address' => 'Jl. Klinik No. 1',
                'phone' => '021-123456',
                'email' => 'sandbox@klinik.local',
                'city' => 'Makassar',
                'is_active' => true,
            ]
        );

        $this->branch = Branch::updateOrCreate(
            ['short_name' => RuntimeCompanySeeder::DEMO_BRANCH_SHORT_NAME],
            ['name' => 'Klinik Demo', 'is_active' => true, 'company_uuid' => $company->uuid]
        );
    }

    protected function seedUsersAndRoles(): void
    {
        $roles = Role::pluck('uuid', 'name');
        $users = config('libraries.users');

        foreach ($users as $email => $data) {
            // Kolom legacy branch_uuid/company_uuid sudah di-drop dari users;
            // kaitan user ke branch/company via pivot user_branches/user_companies.
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('klinik123'),
                ]
            );

            // Semua user demo terhubung ke branch demo (KDM) sebagai konteks aktif.
            UserCompany::query()->updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $this->companyUuid()],
                ['is_default' => true]
            );
            UserBranch::query()->updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $this->companyUuid(), 'branch_uuid' => $this->branch->uuid],
                ['is_default' => true]
            );

            if (isset($roles[$data['role']])) {
                $user->assignRole($data['role']);
            }
        }
    }

    protected function seedEmployees(): void
    {
        $employees = config('libraries.employees');

        foreach ($employees as $item) {
            $user = User::where('email', $item['email'])->first();
            Employee::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'name' => $item['name']],
                [
                    'user_uuid' => $user?->uuid, 
                    'company_uuid' => $this->companyUuid(),
                    'role' => $item['role'], 
                    'fee_default' => $item['fee_default'], 
                    'is_active' => true
                ]
            );
        }
    }

    protected function seedPatients(): void
    {
        $patients = config('libraries.patients');

        foreach ($patients as $item) {
            Patient::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'medical_record_no' => $item['medical_record_no']],
                $item + ['is_active' => true, 'company_uuid' => $this->companyUuid()]
            );
        }
    }

    protected function seedSuppliers(): void
    {
        $suppliers = config('libraries.suppliers');

        foreach ($suppliers as $item) {
            Supplier::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'name' => $item['name']], 
                $item + ['is_active' => true, 'company_uuid' => $this->companyUuid()]
            );
        }
    }

    protected function seedProducts(): void
    {
        $products = config('libraries.products');

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'code' => $item['code']], 
                $item + ['is_active' => true, 'company_uuid' => $this->branch->company_uuid]
            );
        }
    }

    protected function seedServices(): void
    {
        $services = config('libraries.services');

        foreach ($services as $item) {
            Service::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'code' => $item['code']], 
                $item + ['is_active' => true, 'company_uuid' => $this->companyUuid()]
            );
        }
    }

    protected function seedServicePackages(): void
    {
        $package = ServicePackage::updateOrCreate(
            ['branch_uuid' => $this->branch->uuid, 'code' => 'MCU-1'],
            ['name' => 'MCU-1: Cek Kesehatan Dasar', 'price' => 150000, 'is_active' => true, 'company_uuid' => $this->companyUuid()]
        );

        $service = $this->findBranchScoped(Service::class, ['code' => 'SV-001']);
        $product = $this->findBranchScoped(Product::class, ['code' => 'AMX-500']);
        
        if ($package && $service) {
            ServicePackageItem::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'service_package_uuid' => $package->uuid, 'item_type' => 'service', 'item_uuid' => $service->uuid],
                ['qty' => 1, 'company_uuid' => $this->companyUuid()]
            );
        }
        if ($package && $product) {
            ServicePackageItem::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'service_package_uuid' => $package->uuid, 'item_type' => 'product', 'item_uuid' => $product->uuid],
                ['qty' => 1, 'company_uuid' => $this->companyUuid()]
            );
        }
    }

    protected function seedChartOfAccounts(): void
    {
        $accounts = config('libraries.accounts');

        foreach ($accounts as $item) {
            $parentCode = $item['parent_code'];
            unset($item['parent_code']);
            
            $account = Account::updateOrCreate(
                ['code' => $item['code'], 'branch_uuid' => $this->branch->uuid],
                $item + [
                    'is_active' => true,
                    'branch_uuid' => $this->branch->uuid,
                    'company_uuid' => $this->companyUuid(),
                ]
            );

            if ($parentCode) {
                $parent = Account::where('code', $parentCode)->first();
                $account->update(['parent_uuid' => $parent?->uuid]);
            }
        }

        $this->accountMap = Account::all()->keyBy('code')->toArray();
    }

    protected function seedAccountMappings(): void
    {
        $mappings = config('libraries.account_mappings');

        foreach ($mappings as $item) {
            AccountMapping::updateOrCreate(
                ['key' => $item['key'], 'branch_uuid' => $this->branch->uuid],
                [
                    'menu_name' => $item['menu_name'],
                    'account_uuid' => $this->accountMap[$item['code']]['uuid'],
                    'type_saldo' => $item['type_saldo'] ?? 'debit',
                    'note' => $item['note'] ?? null,
                    'value_source' => $item['value_source'] ?? null,
                    'company_uuid' => $this->branch->company_uuid,
                ]
            );
        }

        // Tax Settings (Pajak)
        TaxSetting::updateOrCreate(
            ['name' => 'PPN'],
            [
                'percentage' => 11.00, 
                'account_uuid' => $this->accountMap['2106']['uuid']
            ]
        );
    }

    protected function seedOpeningBalances(): void
    {
        $openingBalances = [
            '1101' => 100000000, // Kas: 100jt
            '1301' => 50000000,  // Stok: 50jt
            '3100' => 150000000, // Modal: 150jt (Credit)
        ];

        foreach ($openingBalances as $code => $balance) {
            AccountBalance::updateOrCreate(
                [
                    'branch_uuid' => $this->branch->uuid, 
                    'account_uuid' => $this->accountMap[$code]['uuid'], 
                    'period' => 'Opening'
                ],
                [
                    'opening_balance' => $balance, 
                    'closing_balance' => $balance,
                    'company_uuid' => $this->branch->company_uuid
                ]
            );
        }
    }

    protected function seedDemoTransactions(): void
    {
        $doctor = $this->findBranchScoped(Employee::class, ['role' => 'dokter']);
        $cashier = $this->findBranchScoped(Employee::class, ['role' => 'kasir']);
        $patient = $this->findBranchScoped(Patient::class, ['medical_record_no' => 'RM-20240001']);
        $service = $this->findBranchScoped(Service::class, ['code' => 'SV-001']);
        $product = $this->findBranchScoped(Product::class, ['code' => 'AMX-500']);
        $supplier = Supplier::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('company_uuid', $this->companyUuid())
            ->orderBy('name')
            ->first();

        // 1. Registration & Encounter
        if ($patient) {
            $registration = Registration::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'queue_no' => 'A-001', 'patient_uuid' => $patient->uuid],
                ['doctor_uuid' => $doctor?->uuid, 'status' => 'done', 'registered_at' => time()]
            );

            $encounter = Encounter::updateOrCreate(
                ['registration_uuid' => $registration->uuid],
                ['branch_uuid' => $this->branch->uuid, 'patient_uuid' => $patient->uuid, 'doctor_uuid' => $doctor?->uuid, 'complaint' => 'Demam dan pusing', 'diagnosis' => 'ISPA', 'status' => 'posted']
            );

            if ($encounter && $service) {
                EncounterItem::updateOrCreate(
                    ['branch_uuid' => $this->branch->uuid, 'encounter_uuid' => $encounter->uuid, 'item_type' => 'service', 'item_uuid' => $service->uuid],
                    ['qty' => 1, 'price' => $service->price, 'subtotal' => $service->price]
                );
            }
        }

        // 2. POS Sale
        $sale = Sale::updateOrCreate(
            ['sale_no' => 'INV-POS-20260303-213032-921'],
            [
                'branch_uuid' => $this->branch->uuid, 
                'company_uuid' => $this->branch->company_uuid,
                'patient_uuid' => $patient?->uuid, 
                'cashier_uuid' => $cashier?->uuid, 
                'total_amount' => 50000, 
                'paid' => 50000, 
                'change' => 0, 
                'payment_status' => 'paid', 
                'sale_at' => time()
            ]
        );

        if ($sale && $product) {
            SaleItem::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->branch->company_uuid, 'sale_uuid' => $sale->uuid, 'item_type' => 'product', 'item_uuid' => $product->uuid],
                ['qty' => 2, 'price' => 25000, 'subtotal' => 50000]
            );
            
            Receivable::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'reference_type' => 'sale', 'reference_uuid' => $sale->uuid],
                [
                    'company_uuid' => $this->branch->company_uuid,
                    'amount' => $sale->total_amount, 
                    'paid' => $sale->paid, 
                    'status' => 'paid'
                ]
            );

            StockMovement::updateOrCreate(
                ['product_uuid' => $product->uuid, 'type' => 'out', 'reference_type' => 'sale', 'reference_uuid' => $sale->uuid],
                ['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->branch->company_uuid, 'qty' => 2]
            );
        }

        // 3. Purchase
        $purchase = Purchase::updateOrCreate(
            ['invoice_no' => 'PO-227D43D'],
            [
                'branch_uuid' => $this->branch->uuid, 
                'company_uuid' => $this->branch->company_uuid,
                'supplier_uuid' => $supplier?->uuid, 
                'total' => 4233023, 
                'paid' => 4233023, 
                'status' => 'paid', 
                'purchase_date' => time()
            ]
        );

        if ($purchase && $product) {
            PurchaseItem::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->branch->company_uuid, 'purchase_uuid' => $purchase->uuid, 'product_uuid' => $product->uuid],
                ['qty' => 50, 'price' => 11538, 'subtotal' => 576900]
            );

            Payable::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'reference_type' => 'purchase', 'reference_uuid' => $purchase->uuid],
                [
                    'company_uuid' => $this->branch->company_uuid,
                    'amount' => $purchase->total, 
                    'paid' => $purchase->paid, 
                    'status' => 'paid'
                ]
            );

            StockMovement::updateOrCreate(
                ['product_uuid' => $product->uuid, 'type' => 'in', 'reference_type' => 'purchase', 'reference_uuid' => $purchase->uuid],
                ['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->branch->company_uuid, 'qty' => 50]
            );
        }
    }

    protected function seedJournalEntries(): void
    {
        $sale = Sale::where('sale_no', 'INV-POS-20260303-213032-921')->first();
        if (!$sale) return;

        $journal = Journal::updateOrCreate(
            ['branch_uuid' => $this->branch->uuid, 'reference_no' => 'REF-SAL-20260303-213032-921'],
            [
                'transaction_code' => $this->branch->short_name . '/SAL/' . date('Ym') . '/0001',
                'reference_model' => Sale::class,
                'reference_type' => 'sale',
                'reference_uuid' => $sale->uuid,
                'journal_date' => time(),
                'description' => 'Jurnal Penjualan POS',
                'status' => 'posted'
            ]
        );

        if ($journal) {
            JournalLine::updateOrCreate(
                ['journal_uuid' => $journal->uuid, 'account_uuid' => $this->accountMap['1101']['uuid'], 'branch_uuid' => $this->branch->uuid],
                ['debit' => 50000, 'credit' => 0, 'is_processed' => true]
            );
            JournalLine::updateOrCreate(
                ['journal_uuid' => $journal->uuid, 'account_uuid' => $this->accountMap['4102']['uuid'], 'branch_uuid' => $this->branch->uuid],
                ['debit' => 0, 'credit' => 50000, 'is_processed' => true]
            );
        }

        // Seed Account Balances for the current period
        foreach ($this->accountMap as $code => $account) {
            AccountBalance::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'account_uuid' => $account['uuid'], 'period' => date('Y-m')],
                [
                    'opening_balance' => 0,
                    'company_uuid' => $this->companyUuid(),
                    'total_debit' => ($code == '1101') ? 50000 : 0,
                    'total_credit' => ($code == '4102') ? 50000 : (($code == '4101') ? 0 : 0),
                    'closing_balance' => ($code == '1101') ? 50000 : (($code == '4101') ? -50000 : 0)
                ]
            );
        }
    }

    protected function seedClosingPeriods(): void
    {
        ClosingPeriod::withoutEvents(function() {
            // Seed in chronological order (oldest first) to avoid Gap/Overlap validation errors
            for ($i = 2; $i >= 0; $i--) {
                $date = now()->subMonthsNoOverflow($i);
                ClosingPeriod::updateOrCreate(
                    ['branch_uuid' => $this->branch->uuid, 'period' => $date->format('Y-m')],
                    [
                        'status' => 'open',
                        'company_uuid' => $this->companyUuid(),
                        'name' => $date->format('F Y'),
                        'start_date' => $date->copy()->startOfMonth()->timestamp,
                        'end_date' => $date->copy()->endOfMonth()->timestamp,
                    ]
                );
            }
        });
    }
}
