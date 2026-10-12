<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Entities\AdjustmentEntity;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class AdjustingJournalTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branch;
    protected $user;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($this->user);

        // Setup Period Januari 2026
        $this->period = ClosingPeriod::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open'
        ]);

        $this->service = app(AccountingCycleService::class);
    }

    /**
     * Skenario 1: Generate Jurnal Penyesuaian - Akrual Beban
     */
    public function test_scenario_1_accrual_expense_generation()
    {
        // Setup Accounts
        $expenseAccount = Account::factory()->create(['code' => '501001', 'name' => 'Beban Gaji', 'type' => 'expense']);
        $payableAccount = Account::factory()->create(['code' => '201001', 'name' => 'Utang Gaji', 'type' => 'liability']);

        // Simulasi stored procedure call (Mocking SP behavior for now as SP is real but test db is fresh)
        // In real test, we call $this->service->generateAdjustingJournals($this->period->uuid, $this->branch->uuid);
        
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'period_uuid' => $this->period->uuid,
            'description' => 'Akrual Gaji Januari 2026',
            'amount' => 10000000,
            'debit_account_uuid' => $expenseAccount->uuid,
            'credit_account_uuid' => $payableAccount->uuid,
            'type' => 'accrual_expense'
        ];

        $journal = $this->service->generateAdjustingJournal(AdjustmentEntity::fromArray($data));

        $this->assertNotNull($journal);
        $this->assertEquals('ADJ', $journal->type);
        $this->assertTrue($journal->is_adjusting);
        $this->assertCount(2, $journal->lines);
        
        $this->assertDatabaseHas('journals', ['uuid' => $journal->uuid, 'is_adjusting' => 1]);
    }

    /**
     * Skenario 2: Generate Jurnal Penyesuaian - Prepaid & Penyusutan
     */
    public function test_scenario_2_prepaid_and_depreciation_generation()
    {
        // Prepaid Expense (Asuransi)
        $prepaidAccount = Account::factory()->create(['code' => '102001', 'name' => 'Asuransi Dimuka', 'type' => 'asset']);
        $expenseAccount = Account::factory()->create(['code' => '503001', 'name' => 'Beban Asuransi', 'type' => 'expense']);

        $dataPrepaid = [
            'branch_uuid' => $this->branch->uuid,
            'period_uuid' => $this->period->uuid,
            'description' => 'Amortisasi Asuransi Jan 2026',
            'amount' => 1000000,
            'debit_account_uuid' => $expenseAccount->uuid,
            'credit_account_uuid' => $prepaidAccount->uuid,
            'type' => 'prepaid_expense'
        ];

        $journalPrepaid = $this->service->generateAdjustingJournal(AdjustmentEntity::fromArray($dataPrepaid));
        $this->assertEquals(1000000, $journalPrepaid->lines->firstWhere('debit', '>', 0)->debit);

        // Depreciation
        $assetAccount = Account::factory()->create(['code' => '103001', 'name' => 'Peralatan Medis', 'type' => 'asset']);
        $accDepAccount = Account::factory()->create(['code' => '103001-ACC', 'name' => 'Akumulasi Penyusutan', 'type' => 'asset']);
        $depExpenseAccount = Account::factory()->create(['code' => '504001', 'name' => 'Beban Penyusutan', 'type' => 'expense']);

        $dataDep = [
            'branch_uuid' => $this->branch->uuid,
            'period_uuid' => $this->period->uuid,
            'description' => 'Penyusutan Peralatan Jan 2026',
            'amount' => 500000,
            'debit_account_uuid' => $depExpenseAccount->uuid,
            'credit_account_uuid' => $accDepAccount->uuid,
            'type' => 'depreciation'
        ];

        $journalDep = $this->service->generateAdjustingJournal(AdjustmentEntity::fromArray($dataDep));
        $this->assertEquals(500000, $journalDep->lines->where('credit', '>', 0)->first()->credit);
    }

    /**
     * Skenario 13: Idempotency - Generate Ulang Tidak Double
     */
    public function test_scenario_13_idempotency_validation()
    {
        // Ensure period exists (it should from setUp, but let's be sure it's not stale)
        $this->assertDatabaseHas('closing_periods', ['uuid' => $this->period->uuid]);

        Journal::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'is_adjusting' => true,
            'journal_date' => $this->period->start_date
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Adjusting journals for this period already exist.");
        
        $this->service->generateAdjustingJournals(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));
    }

    /**
     * Skenario 14: Rollback & Recovery saat Generate Gagal
     */
    public function test_scenario_14_transactional_rollback()
    {
        $countBefore = Journal::count();

        try {
            DB::transaction(function() {
                Journal::create(['description' => 'Partial 1', 'branch_uuid' => $this->branch->uuid, 'type' => 'ADJ', 'journal_date' => now()]);
                throw new \Exception("Simulated Failure");
            });
        } catch (\Exception $e) {
            // Expected
        }

        $this->assertEquals($countBefore, Journal::count(), "Database should rollback on failure");
    }
}
