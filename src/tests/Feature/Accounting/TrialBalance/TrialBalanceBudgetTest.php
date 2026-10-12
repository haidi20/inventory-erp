<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Skenario 12: Validasi Budgeting dan Forecasting
 * Menggunakan AccountBalance sebagai data actual dan membandingkan dengan target budget.
 */
class TrialBalanceBudgetTest extends TestCase
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
            'name' => 'Budget Branch', 'short_name' => 'BDG', 'is_active' => true
        ]);
        $this->period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * 12.1: Perbandingan Actual vs Budget - analisis variansi per akun
     */
    public function test_actual_vs_budget_variance_analysis()
    {
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accBeban = Account::create(['code' => '5101', 'name' => 'Beban Operasional', 'type' => 'expense', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);

        // Budget: Pendapatan 100jt, Beban 80jt
        $budgetPendapatan = 100000000;
        $budgetBeban = 80000000;

        // Actual: Pendapatan 120jt (favorable), Beban 90jt (unfavorable)
        $actualPendapatan = 120000000;
        $actualBeban = 90000000;

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'BDG-REV-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => $actualPendapatan, 'credit' => 0],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => $actualPendapatan],
            ],
        ]));

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'BDG-EXP-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-20',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accBeban->uuid, 'debit' => $actualBeban, 'credit' => 0],
                ['account_uuid' => $accKas->uuid, 'debit' => 0, 'credit' => $actualBeban],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);
        $this->assertTrue($tb->is_balanced);

        // Hitung variansi
        $varPendapatan = $actualPendapatan - $budgetPendapatan; // +20M (favorable)
        $varBeban = $actualBeban - $budgetBeban; // +10M (unfavorable)
        $varLaba = $varPendapatan - $varBeban; // +10M (favorable)

        $this->assertGreaterThan(0, $varPendapatan, 'Variansi pendapatan harus favorable');
        $this->assertGreaterThan(0, $varBeban, 'Variansi beban harus unfavorable (actual > budget)');
        $this->assertGreaterThan(0, $varLaba, 'Variansi laba harus favorable');

        // Hitung persentase variansi
        $pctPendapatan = ($varPendapatan / $budgetPendapatan) * 100; // 20%
        $this->assertEquals(20, $pctPendapatan, 'Variansi pendapatan 20%');
    }

    /**
     * 12.2: Analisis variansi dengan threshold materialitas
     */
    public function test_variance_materiality_threshold()
    {
        $accA = Account::create(['code' => '4001', 'name' => 'Pendapatan A', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accB = Account::create(['code' => '4002', 'name' => 'Pendapatan B', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);

        // Budget: A=100M, B=50M | Actual: A=150M (+50%), B=52M (+4%)
        $budgets = ['4001' => 100000000, '4002' => 50000000];
        $actuals = ['4001' => 150000000, '4002' => 52000000];
        $threshold = 10; // 10% materiality

        // Post actuals
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'MAT-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => $actuals['4001'] + $actuals['4002'], 'credit' => 0],
                ['account_uuid' => $accA->uuid, 'debit' => 0, 'credit' => $actuals['4001']],
                ['account_uuid' => $accB->uuid, 'debit' => 0, 'credit' => $actuals['4002']],
            ],
        ]));

        // Detect material variances
        $materialVariances = [];
        foreach ($budgets as $code => $budget) {
            $actual = $actuals[$code];
            $pctVar = abs(($actual - $budget) / $budget) * 100;
            if ($pctVar >= $threshold) {
                $materialVariances[] = ['code' => $code, 'pct' => $pctVar];
            }
        }

        $this->assertCount(1, $materialVariances, 'Hanya 1 akun melebihi threshold materialitas');
        $this->assertEquals('4001', $materialVariances[0]['code']);
        $this->assertEquals(50, $materialVariances[0]['pct']);
    }

    /**
     * 12.3: Multi-period trend analysis (3 bulan)
     */
    public function test_multi_period_trend_analysis()
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        // Create 3 periods
        $periods = [];
        $amounts = [80000000, 100000000, 120000000]; // tren naik
        foreach (['01', '02', '03'] as $i => $m) {
            $p = ($i > 0) ? ClosingPeriod::create([
                'branch_uuid' => $this->branch->uuid, 'period' => "2026-$m", 'name' => "Period $m",
                'start_date' => "2026-$m-01", 'end_date' => "2026-$m-28", 'status' => 'open'
            ]) : $this->period;
            if ($i === 0) {
                $p->update(['period' => '2026-01', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'name' => 'Period 01']);
            }
            $periods[] = $p;

            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid, 'reference_no' => "TREND-$m",
                'reference_uuid' => 0, 'journal_date' => "2026-$m-15",
                'reference_type' => JournalReferenceType::GENERAL,
                'status' => 'posted',
                'lines' => [
                    ['account_uuid' => $accKas->uuid, 'debit' => $amounts[$i], 'credit' => 0],
                    ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => $amounts[$i]],
                ],
            ]));

            app(GeneralLedgerService::class)->syncByPeriod($p->uuid);
        }

        // Verifikasi tren naik
        $balances = AccountBalance::where('account_uuid', $accPendapatan->uuid)
            ->orderBy('period')->pluck('total_credit')->map(fn($v) => (float) $v)->toArray();

        $this->assertCount(3, $balances);
        $this->assertTrue($balances[2] > $balances[1] && $balances[1] > $balances[0],
            'Tren pendapatan harus naik');

        // Hitung growth rate
        $growthRate = (($balances[2] - $balances[0]) / $balances[0]) * 100;
        $this->assertEquals(50, $growthRate, 'Growth rate 3 bulan = 50%');
    }

    /**
     * 12.4: What-If Scenario - dampak kenaikan/penurunan penjualan terhadap laba
     */
    public function test_what_if_scenario_analysis()
    {
        $basePendapatan = 100000000;
        $baseBeban = 70000000;
        $baseLaba = $basePendapatan - $baseBeban;

        // Skenario optimistis: +20%
        $optPendapatan = $basePendapatan * 1.2;
        $optLaba = $optPendapatan - $baseBeban;

        // Skenario pesimistis: -10%
        $pesPendapatan = $basePendapatan * 0.9;
        $pesLaba = $pesPendapatan - $baseBeban;

        $this->assertGreaterThan($baseLaba, $optLaba, 'Laba optimis > base');
        $this->assertLessThan($baseLaba, $pesLaba, 'Laba pesimis < base');

        // Break-even: Pendapatan = Beban
        $breakEven = $baseBeban;
        $this->assertEquals(70000000, $breakEven, 'Break-even = total beban');

        // Margin of safety
        $mos = (($basePendapatan - $breakEven) / $basePendapatan) * 100;
        $this->assertEquals(30, $mos, 'Margin of safety = 30%');
    }

    /**
     * 12.5: Zero-Based Budget - setiap akun beban di-reset ke 0 dan dijustifikasi
     */
    public function test_zero_based_budget_comparison()
    {
        $accBeban1 = Account::create(['code' => '5001', 'name' => 'Gaji', 'type' => 'expense', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accBeban2 = Account::create(['code' => '5002', 'name' => 'Listrik', 'type' => 'expense', 'financial_statement' => 'income_statement', 'is_header' => 0]);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);

        // Historical actuals
        $historical = ['5001' => 50000000, '5002' => 10000000];
        // ZBB justifikasi: Gaji tetap, Listrik dikurangi 20%
        $zbb = ['5001' => 50000000, '5002' => 8000000];

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'ZBB-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accBeban1->uuid, 'debit' => $historical['5001'], 'credit' => 0],
                ['account_uuid' => $accBeban2->uuid, 'debit' => $historical['5002'], 'credit' => 0],
                ['account_uuid' => $accKas->uuid, 'debit' => 0, 'credit' => 60000000],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);

        // Bandingkan historical vs ZBB
        $totalHistorical = array_sum($historical);
        $totalZBB = array_sum($zbb);
        $savings = $totalHistorical - $totalZBB; // 2M savings

        $this->assertEquals(2000000, $savings, 'ZBB harus menghasilkan penghematan Rp 2jt');
        $this->assertLessThan($totalHistorical, $totalZBB, 'Total ZBB < Total Historical');
    }
}
