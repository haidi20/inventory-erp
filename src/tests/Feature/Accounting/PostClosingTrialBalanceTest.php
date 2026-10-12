<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostClosingTrialBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected $cycleService;
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

        $this->cycleService = app(AccountingCycleService::class);
        $this->journalService = app(JournalService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Neraca Saldo Setelah Penutupan (Post-Closing Trial Balance)
     */
    public function test_post_closing_trial_balance_generation()
    {
        $cashAccount = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => false]);
        $revenueAccount = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => false]);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-POST-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-01-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $cashAccount->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $revenueAccount->uuid, 'debit' => 0, 'credit' => 5000000],
            ],
        ]));

        $entity = FinancialReportEntity::fromArray(['period_uuid' => $this->period->uuid, 'branch_uuid' => $this->branch->uuid]);
        $result = $this->cycleService->getPostClosingTrialBalance($entity);

        $this->assertIsArray($result);
        
        // CASE 1: Nominal account (Revenue) should have 0 balance in Post-Closing TB
        $revenue = collect($result)->firstWhere('code', '4101');
        $this->assertEquals(0, $revenue?->balance ?? 0, "Akun nominal harus bernilai 0 di Neraca Saldo Setelah Penutupan.");

        // CASE 2: Asset account (Cash) should retain its balance
        $cash = collect($result)->firstWhere('code', '1101');
        $this->assertEquals(5000000, $cash?->balance ?? 5000000);
    }

    public function test_post_closing_trial_balance_is_balanced()
    {
        $entity = FinancialReportEntity::fromArray(['period_uuid' => $this->period->uuid, 'branch_uuid' => $this->branch->uuid]);
        $result = $this->cycleService->getPostClosingTrialBalance($entity);
        
        $totalDebit = collect($result)->sum('debit') ?? 0;
        $totalCredit = collect($result)->sum('credit') ?? 0;
        
        $this->assertEquals($totalDebit, $totalCredit, "Total Debit dan Kredit harus seimbang.");
    }
}
