<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $cycleService;
    protected $branch;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->reportService = app(FinancialReportService::class);
        $this->cycleService = app(AccountingCycleService::class);
    }

    /**
     * Skenario 4: Generate Laporan Laba Rugi
     */
    public function test_scenario_4_income_statement()
    {
        $entity = FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $result = $this->reportService->getIncomeStatement($entity);
        $this->assertIsArray($result);
    }

    /**
     * Skenario 5: Generate Neraca (Balance Sheet)
     */
    public function test_scenario_5_balance_sheet()
    {
        $entity = FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $result = $this->reportService->getBalanceSheet($entity);
        $this->assertIsArray($result);
    }

    /**
     * Skenario 6: Generate Laporan Arus Kas
     */
    public function test_scenario_6_cash_flow()
    {
        $entity = FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $result = $this->cycleService->getCashFlow($entity);
        $this->assertIsArray($result);
    }
}
