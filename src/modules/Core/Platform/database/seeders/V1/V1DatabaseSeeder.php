<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class V1DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // Bersihkan data lama untuk mencegah duplikasi (kecuali tabel inti yang dihandle seeder)
        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        ClosingPeriod::truncate();
        Account::truncate();
        User::truncate();
        Employee::truncate();
        Patient::truncate();
        Supplier::truncate();
        Product::truncate();
        Service::truncate();
        Unit::truncate();

        Model::unguard();
        Sale::truncate();
        SaleItem::truncate();
        Purchase::truncate();
        PurchaseItem::truncate();
        Registration::truncate();
        Encounter::truncate();
        EncounterItem::truncate();
        Journal::truncate();
        JournalLine::truncate();

        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Nonaktifkan events penutupan buku untuk stabilitas seeding seluruh pilar
        ClosingPeriod::withoutEvents(function () {
            $this->call([
                RuntimeCompanySeeder::class,
                RolePermissionSeeder::class,
                DemoSeeder::class,
                ChartOfAccountsSeeder::class,
                PatientsSeeder::class,
                ClinicalServiceCatalogSeeder::class,
                Icd10Seeder::class,
                Icd9Seeder::class,
                BpjsSettingsSeeder::class,
                SatusehatSettingsSeeder::class,
                LookupSeeder::class,
                LookupValueSeeder::class,
                UnitSeeder::class,
                WarehouseSeeder::class,
                ProductSeeder::class,
                ProductFormEditFixtureSeeder::class,
                TaxSeeder::class,
                PaymentTermSeeder::class,
                RuntimeUserSeeder::class,
            ]);

            $this->call([
                JournalSimulationSaleSeeder::class,
                JournalSimulationPurchaseSeeder::class,
                JournalSimulationEncounterSeeder::class,
                DoctorConsultationWorkflowSeeder::class,
                JournalSimulationExpenseSeeder::class,
                JournalSimulationDebtPaymentSeeder::class,
                JournalSimulationReceivableCollectionSeeder::class,
            ]);
        });
    }
}
