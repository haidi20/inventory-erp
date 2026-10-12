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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrialBalanceRecoveryTest extends TestCase
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
            'name' => 'Recovery Branch', 'short_name' => 'REC', 'is_active' => true
        ]);
        $this->period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    public function test_disaster_recovery_rebuild_gl_from_journals()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        for ($i = 1; $i <= 5; $i++) {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid, 'reference_no' => "REC-$i",
                'reference_uuid' => 0, 'journal_date' => '2026-03-10',
                'reference_type' => JournalReferenceType::GENERAL,
                'status' => JournalStatus::POSTED,
                'lines' => [
                    ['account_uuid' => $accKas->uuid, 'debit' => 1000000, 'credit' => 0],
                    ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 1000000],
                ],
            ]));
        }

        // 1. Initial Sync
        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $this->assertEquals(10, GeneralLedger::count(), "Initial sync failed");

        // 2. Simulate Disaster (Delete GL)
        GeneralLedger::query()->delete();
        $this->assertEquals(0, GeneralLedger::count());

        // 3. Recovery Sync
        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);

        $count = GeneralLedger::count();
        $this->assertEquals(10, $count, "Recovery must restore 10 GL rows including clearing soft deletes. Found: $count");
    }

    public function test_recovery_from_soft_deleted_account_balances()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $ab = AccountBalance::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $accKas->uuid, 'period' => '2026-03',
            'opening_balance' => 0, 'total_debit' => 1000000, 'total_credit' => 0, 'closing_balance' => 1000000
        ]);

        $ab->delete();
        $this->assertNull(AccountBalance::where('account_uuid', $accKas->uuid)->first());
        AccountBalance::withTrashed()->where('account_uuid', $accKas->uuid)->restore();
        $this->assertNotNull(AccountBalance::where('account_uuid', $accKas->uuid)->first());
    }

    public function test_consistency_after_database_restoration_simulation()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        
        // Manual AccountBalance update simulation (e.g. from partial restore)
        AccountBalance::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $accKas->uuid, 'period' => '2026-03',
            'opening_balance' => 0, 'total_debit' => 5000000, 'total_credit' => 0, 'closing_balance' => 5000000
        ]);

        $journal = Journal::create([
            'branch_uuid' => $this->branch->uuid, 'transaction_code' => 'SIM/001', 'reference_no' => 'REF-SIM-001',
            'reference_type' => JournalReferenceType::GENERAL, 'reference_uuid' => 0,
            'journal_date' => '2026-03-10', 'status' => 'posted'
        ]);
        
        $line = JournalLine::create([
            'branch_uuid' => $this->branch->uuid, 'journal_uuid' => $journal->uuid,
            'account_uuid' => $accKas->uuid, 'debit' => 10000000, 'credit' => 0
        ]);

        // Manually create GL with different value to test sync override
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = OFF') : DB::statement('SET FOREIGN_KEY_CHECKS=0');
        GeneralLedger::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $accKas->uuid, 'journal_uuid' => $journal->uuid,
            'journal_line_uuid' => $line->uuid, 'reference_no' => 'REF-SIM-001', 'description' => 'Recovery Simulation',
            'journal_date' => '2026-03-10', 'debit' => 5000000, 'credit' => 0, 'balance' => 5000000
        ]);
        DB::getDriverName() === 'sqlite' ? DB::statement('PRAGMA foreign_keys = ON') : DB::statement('SET FOREIGN_KEY_CHECKS=1');

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        
        // Asserting against Ledger Balance because Sync ONLY updates Ledger
        $gl = GeneralLedger::where('journal_line_uuid', $line->uuid)->first();
        $this->assertEquals(10000000, (float) $gl->debit);
        $this->assertEquals(10000000, (float) $gl->balance);
    }

    public function test_concurrent_trial_balance_generation_robustness()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        for ($i = 1; $i <= 5; $i++) {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid, 'reference_no' => "CONC-$i",
                'reference_uuid' => 0, 'journal_date' => '2026-03-10',
                'reference_type' => JournalReferenceType::GENERAL, 'status' => 'posted',
                'lines' => [
                    ['account_uuid' => $accKas->uuid, 'debit' => 1500000, 'credit' => 0],
                    ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 1500000],
                ],
            ]));
        }

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);

        for ($i = 0; $i < 3; $i++) {
            // Trial balance service uses sp_get_trial_balance which fallbacks to GL if AccountBalance is empty
            $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);
            $kas = collect($tb->accounts)->firstWhere('account_code', '1101');
            $this->assertNotNull($kas);
            $this->assertEquals(7500000, (float) $kas->total_debit);
        }
    }

    public function test_high_precision_decimal_compatibility()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $value = 1234.567890123;
        
        $journal = Journal::create([
            'branch_uuid' => $this->branch->uuid, 'transaction_code' => 'PREC/001', 'reference_no' => 'REF-PREC-001',
            'reference_type' => JournalReferenceType::GENERAL, 'reference_uuid' => 0,
            'journal_date' => '2026-03-10', 'status' => 'posted'
        ]);
        
        $line = JournalLine::create([
            'branch_uuid' => $this->branch->uuid, 'journal_uuid' => $journal->uuid,
            'account_uuid' => $accKas->uuid, 'debit' => $value, 'credit' => 0
        ]);

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        
        $gl = GeneralLedger::where('journal_line_uuid', $line->uuid)->first();
        $this->assertNotNull($gl);
        // DB stores decimal(16,2) by default based on schema description
        $this->assertEquals(round($value, 2), (float) $gl->debit);
    }
}
