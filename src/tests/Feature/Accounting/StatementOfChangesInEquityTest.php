<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * StatementOfChangesInEquityTest
 * 
 * Verifikasi laporan perubahan modal (Modal Awal, Laba Rugi, Setoran, Prive).
 */
class StatementOfChangesInEquityTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $journalService;
    protected $branch;
    protected $period;
    protected $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create(['name' => 'Cabang Test']);
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->service = app(FinancialReportService::class);
        $this->journalService = app(JournalService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        $this->setupAccounts();
    }

    private function setupAccounts()
    {
        $this->accounts['equity'] = Account::create(['code' => '3101', 'name' => 'Modal Pemilik', 'type' => 'equity', 'is_header' => false]);
        $this->accounts['prive'] = Account::create(['code' => '3102', 'name' => 'Prive Pemilik', 'type' => 'equity', 'is_header' => false]);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'is_header' => false]);
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'is_header' => false]);
    }

    private function postJournal(string $date, array $lines, $refNo = 'TR-EQ-001')
    {
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => $refNo . '-' . uniqid(),
            'reference_uuid' => 0,
            'journal_date' => $date,
            'period_uuid' => $this->period->uuid, // Force period association
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => $lines,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);

        return $journal;
    }

    public function test_initial_equity_balance()
    {
        $result = $this->service->getEquityStatement($this->period->uuid, $this->branch->uuid);
        $this->assertEquals(0, $result['initial_equity'] ?? 0);
    }

    public function test_net_income_link_to_equity()
    {
        // 1. Post Revenue 10jt
        $this->postJournal('2026-01-15', [
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 10000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 10000000],
        ], 'TR-REVENUE');

        $result = $this->service->getEquityStatement($this->period->uuid, $this->branch->uuid);
        $this->assertEquals(10000000, $result['net_income'] ?? 0);
    }

    public function test_additional_capital_investment()
    {
        // Setoran Modal 50jt
        $this->postJournal('2026-01-05', [
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 50000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['equity']->uuid, 'debit' => 0, 'credit' => 50000000],
        ], 'TR-CAPITAL');

        $result = $this->service->getEquityStatement($this->period->uuid, $this->branch->uuid);
        $this->assertEquals(50000000, $result['additional_capital'] ?? 0);
    }

    public function test_prive_withdrawal()
    {
        // Prive 5jt
        $this->postJournal('2026-01-20', [
            ['account_uuid' => $this->accounts['prive']->uuid, 'debit' => 5000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 5000000],
        ], 'TR-PRIVE');

        $result = $this->service->getEquityStatement($this->period->uuid, $this->branch->uuid);
        $this->assertEquals(5000000, $result['prive'] ?? 0);
    }

    public function test_final_equity_calculation()
    {
        // 1. Post Revenue 10jt
        $this->postJournal('2026-01-15', [
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 10000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 10000000],
        ], 'TR-REVENUE');

        // 2. Setoran Modal 50jt
        $this->postJournal('2026-01-05', [
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 50000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['equity']->uuid, 'debit' => 0, 'credit' => 50000000],
        ], 'TR-CAPITAL');

        // 3. Prive 5jt
        $this->postJournal('2026-01-20', [
            ['account_uuid' => $this->accounts['prive']->uuid, 'debit' => 5000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 5000000],
        ], 'TR-PRIVE');

        $result = $this->service->getEquityStatement($this->period->uuid, $this->branch->uuid);
        
        $this->assertEquals(10000000, $result['net_income'] ?? 0);
        $this->assertEquals(50000000, $result['additional_capital'] ?? 0);
        $this->assertEquals(5000000, $result['prive'] ?? 0);
        $this->assertEquals(55000000, $result['final_equity'] ?? 0);
    }
}
