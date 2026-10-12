<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $models = [
            StockMovement::class,
            Receivable::class,
            Payable::class,
            JournalLine::class,
            Journal::class,
            AccountBalance::class,
            ClosingPeriod::class,
            EncounterItem::class,
            Encounter::class,
            Registration::class,
            PurchaseItem::class,
            Purchase::class,
            SaleItem::class,
            Sale::class,
            Patient::class,
            Employee::class,
            Supplier::class,
            ServicePackageItem::class,
            ServicePackage::class,
            Service::class,
            Product::class,
        ];

        foreach ($models as $model) {
            if (Schema::hasTable((new $model)->getTable())) {
                $model::truncate();
            }
        }

        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('Data testing berhasil dihapus.');
    }
}
