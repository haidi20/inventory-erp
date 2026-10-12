<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClosingCycleTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branch;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->period = ClosingPeriod::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open'
        ]);

        $this->service = app(AccountingCycleService::class);
    }

    /**
     * Skenario 7: Generate Jurnal Penutup
     */
    public function test_scenario_7_closing_journals()
    {
        $result = $this->service->closePeriod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));
        $this->assertIsArray($result);
    }

    /**
     * Skenario 8: Generate Post-Closing Trial Balance
     */
    public function test_scenario_8_post_closing_trial_balance()
    {
        $result = $this->service->getPostClosingTrialBalance(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));
        $this->assertIsArray($result);
    }

    /**
     * Skenario 9: Generate Opening Balance Periode Baru
     */
    public function test_scenario_9_opening_balance_initialization()
    {
        $nextPeriod = ClosingPeriod::factory()->create([
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28'
        ]);

        $result = $this->service->initializeNewPeriod($this->period->uuid, $nextPeriod->uuid, $this->branch->uuid);
        $this->assertIsArray($result);
    }
}
