<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrialBalanceAuditForensicTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected TrialBalanceService $trialBalanceService;
    protected Branch $branch;
    protected ClosingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->trialBalanceService = app(TrialBalanceService::class);

        $this->branch = Branch::factory()->create([
            'name' => 'Audit Branch', 'short_name' => 'AUD', 'is_active' => true
        ]);
        ClosingPeriod::withoutEvents(function () {
            $this->period = ClosingPeriod::create([
                'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
                'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
            ]);
        });

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    public function test_long_term_data_accumulation_consistency()
    {
        $branchLT = Branch::factory()->create(['name' => 'Branch LongTerm', 'short_name' => 'LT', 'is_active' => true]);
        $accKas = Account::create([
            'code' => '1101-LT', 'name' => 'Kas LT', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0,
            'company_uuid' => $branchLT->company_uuid, 'branch_uuid' => $branchLT->uuid,
        ]);
        
        // IMPORTANT: Switch to branch user to satisfy HasBranchScope
        $userLT = User::factory()->create(['branch_uuid' => $branchLT->uuid]);
        $this->actingAs($userLT);

        ClosingPeriod::withoutEvents(function () use ($branchLT) {
            for ($year = 2016; $year <= 2025; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $periodStr = sprintf("%d-%02d", $year, $month);
                    $startDate = sprintf("%d-%02d-01", $year, $month);
                    $endDate = Carbon::parse($startDate)->endOfMonth()->toDateString();
                    
                    ClosingPeriod::create([
                        'branch_uuid' => $branchLT->uuid, 'company_uuid' => $branchLT->company_uuid, 'period' => $periodStr,
                        'name' => "$periodStr", 'start_date' => $startDate, 'end_date' => $endDate, 'status' => 'closed'
                    ]);
                }
            }
        });

        $journal = Journal::create([
            'branch_uuid' => $branchLT->uuid, 'transaction_code' => "TX-LT",
            'reference_no' => "REF-LT", 'reference_type' => JournalReferenceType::GENERAL,
            'reference_uuid' => 0, 'journal_date' => '2025-12-31', 'status' => 'posted'
        ]);

        $line = JournalLine::create([
            'branch_uuid' => $branchLT->uuid, 'journal_uuid' => $journal->uuid,
            'account_uuid' => $accKas->uuid, 'debit' => 10000000, 'credit' => 0
        ]);

        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = OFF') : DB::statement('SET FOREIGN_KEY_CHECKS=0');
        GeneralLedger::create([
            'branch_uuid' => $branchLT->uuid, 'account_uuid' => $accKas->uuid,
            'journal_uuid' => $journal->uuid, 'journal_line_uuid' => $line->uuid,
            'journal_date' => '2025-12-31', 'reference_no' => "REF-LT",
            'description' => 'Accumulation', 'debit' => 10000000, 'credit' => 0, 'balance' => 10000000
        ]);
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = ON') : DB::statement('SET FOREIGN_KEY_CHECKS=1');

        ClosingPeriod::withoutEvents(function () use ($branchLT, &$periodJan26) {
            $periodJan26 = ClosingPeriod::create([
                'branch_uuid' => $branchLT->uuid, 'company_uuid' => $branchLT->company_uuid, 'period' => '2026-01', 'name' => 'Jan 2026',
                'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open'
            ]);
        });

        $tb = $this->trialBalanceService->generate($periodJan26->uuid, $branchLT->uuid, true);
        $kas = collect($tb->accounts)->firstWhere('account_code', '1101-LT');

        $this->assertNotNull($kas);
        $this->assertEquals(10000000, $kas->opening_balance);
    }
}
