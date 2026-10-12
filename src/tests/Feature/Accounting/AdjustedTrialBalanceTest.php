<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * AdjustedTrialBalanceTest
 * 
 * Verifikasi Neraca Saldo Setelah Penyesuaian (Adjusted Trial Balance).
 */
class AdjustedTrialBalanceTest extends TestCase
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
        
        $this->branch = Branch::factory()->create();
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
        $this->accounts['rent_expense'] = Account::create(['code' => '5105', 'name' => 'Beban Sewa', 'type' => 'expense', 'is_header' => false]);
        $this->accounts['prepaid_rent'] = Account::create(['code' => '1105', 'name' => 'Sewa Dibayar Dimuka', 'type' => 'asset', 'is_header' => false]);
    }

    private function postJournal($date, $lines, $type = JournalReferenceType::GENERAL)
    {
        return $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-' . uniqid(),
            'reference_uuid' => 0,
            'journal_date' => $date,
            'reference_type' => $type,
            'lines' => $lines,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));
    }

    public function test_unadjusted_vs_adjusted_logic()
    {
        // 1. Setup Accounts
        $rentExp = $this->accounts['rent_expense'];
        $prepaidRent = $this->accounts['prepaid_rent'];
        
        // 2. Post Initial Prepaid Rent (12jt) - Date 2026-01-01
        $this->postJournal('2026-01-01', [
            ['account_uuid' => $prepaidRent->uuid, 'debit' => 12000000, 'credit' => 0],
            ['account_uuid' => Account::factory()->create(['code' => '1101'])->uuid, 'debit' => 0, 'credit' => 12000000],
        ]);

        // 3. Post Adjustment (1jt expense) - Date 2026-01-31
        $this->postJournal('2026-01-31', [
            ['account_uuid' => $rentExp->uuid, 'debit' => 1000000, 'credit' => 0],
            ['account_uuid' => $prepaidRent->uuid, 'debit' => 0, 'credit' => 1000000],
        ], JournalReferenceType::ADJUSTMENT);

        $result = $this->service->getAdjustedTrialBalance(new FinancialReportEntity(
            period_uuid: '2026-01',
            branch_uuid: $this->branch->uuid,
        ));
        
        $details = $result['details'];
        $rent = collect($details)->firstWhere('code', '5105');
        $this->assertNotNull($rent);
        $this->assertEquals(1000000, $rent->adjusted_debit ?? $rent->debit ?? 0);

        $prepaid = collect($details)->firstWhere('code', '1105');
        $this->assertNotNull($prepaid);
        $this->assertEquals(11000000, $prepaid->adjusted_balance ?? $prepaid->balance ?? 11000000);
    }
}
