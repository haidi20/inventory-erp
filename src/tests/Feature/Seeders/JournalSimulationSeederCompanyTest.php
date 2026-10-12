<?php

namespace Tests\Feature\Seeders;

use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Core\Organization\Models\Branch;
use Database\Seeders\V1\JournalSimulationDebtPaymentSeeder;
use Database\Seeders\V1\ChartOfAccountsSeeder;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\JournalSimulationEncounterSeeder;
use Database\Seeders\V1\JournalSimulationExpenseSeeder;
use Database\Seeders\V1\JournalSimulationPurchaseSeeder;
use Database\Seeders\V1\JournalSimulationReceivableCollectionSeeder;
use Database\Seeders\V1\JournalSimulationSaleSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class JournalSimulationSeederCompanyTest extends TestCase
{
    public function test_it_scopes_journal_simulation_seeders_to_demo_branch_and_company(): void
    {
        ClosingPeriod::withoutEvents(function () {
            $this->seed(RuntimeCompanySeeder::class);
            $this->seed(DemoSeeder::class);
            $this->seed(ChartOfAccountsSeeder::class);
            $this->seed(JournalSimulationSaleSeeder::class);
            $this->seed(JournalSimulationPurchaseSeeder::class);
            $this->seed(JournalSimulationEncounterSeeder::class);
            $this->seed(JournalSimulationExpenseSeeder::class);
            $this->seed(JournalSimulationDebtPaymentSeeder::class);
            $this->seed(JournalSimulationReceivableCollectionSeeder::class);
        });

        $demoBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $this->assertDatabaseHas('sales', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('purchases', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('registrations', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('encounters', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertNotNull(
            Payable::query()
                ->where('branch_uuid', $demoBranch->uuid)
                ->where('company_uuid', $demoBranch->company_uuid)
                ->where('paid', '>', 0)
                ->first()
        );

        $this->assertNotNull(
            Receivable::query()
                ->where('branch_uuid', $demoBranch->uuid)
                ->where('company_uuid', $demoBranch->company_uuid)
                ->where('paid', '>', 0)
                ->first()
        );

        $this->assertNotNull(
            Journal::query()
                ->where('branch_uuid', $demoBranch->uuid)
                ->where('description', 'like', 'Seeded: Biaya Listrik%')
                ->first()
        );
    }
}
