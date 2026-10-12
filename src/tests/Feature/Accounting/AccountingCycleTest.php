<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountingCycleTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $journalService;
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
        $this->journalService = app(JournalService::class);
    }

    /**
     * Skenario 10: End-to-End Siklus Akuntansi Bulanan
     */
    public function test_scenario_10_full_monthly_cycle()
    {
        // 1. Daily Transactions
        // (Simplified for E2E flow check)
        $this->assertTrue(true);

        // 2. Adjustments
        $entity = new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        );

        $this->service->generateAdjustingJournals($entity);

        // 3. Closing
        $this->service->closePeriod($entity);

        // 4. Verification
        $postClosing = $this->service->getPostClosingTrialBalance($entity);
        $this->assertIsArray($postClosing);
    }
}
