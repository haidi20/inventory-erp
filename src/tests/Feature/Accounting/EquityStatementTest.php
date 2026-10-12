<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquityStatementTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $journalService;
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
        $this->journalService = app(JournalService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Laporan Perubahan Ekuitas (Equity Statement)
     */
    public function test_equity_statement_generation()
    {
        // Setup accounts
        $equityAccount = Account::create([
            'code' => '3101',
            'name' => 'Modal Disetor',
            'type' => 'equity',
            'financial_statement' => 'balance_sheet',
            'is_header' => false
        ]);
        
        $retainedEarningsAccount = Account::create([
            'code' => '3901',
            'name' => 'Laba Ditahan',
            'type' => 'equity',
            'financial_statement' => 'balance_sheet',
            'is_header' => false
        ]);

        // Mock some data if needed, but since we use RefreshDatabase and real services, 
        // we can just call the service and assert structure.
        $entity = FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $result = $this->reportService->getEquityStatement($entity);
        
        $this->assertIsArray($result);
        // Assuming the stored procedure returns at least one row or an empty array if no data
    }
}
