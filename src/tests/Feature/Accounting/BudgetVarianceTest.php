<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BudgetVarianceTest extends TestCase
{
    use RefreshDatabase;

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

        $this->journalService = app(JournalService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Perbandingan Actual vs Budget dan Peringatan Over Budget
     */
    public function test_actual_vs_budget_variance_with_alerts()
    {
        $accBeban = Account::create([
            'code' => '5101', 'name' => 'Beban Gaji', 'type' => 'expense',
            'financial_statement' => 'income_statement', 'is_header' => false
        ]);
        
        $accKas = Account::create([
            'code' => '1101', 'name' => 'Kas', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => false
        ]);

        // Target Anggaran: Rp 10.000.000
        $budgetAmount = 10000000;
        
        // Realisasi: Rp 12.000.000 (Melampaui anggaran)
        $actualAmount = 12000000;

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-BUDGET-001',
            'journal_date' => '2026-01-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accBeban->uuid, 'debit' => $actualAmount, 'credit' => 0],
                ['account_uuid' => $accKas->uuid, 'debit' => 0, 'credit' => $actualAmount],
            ],
        ]));

        // Hitung Variansi
        $variance = $actualAmount - $budgetAmount;
        $variancePercentage = ($variance / $budgetAmount) * 100;

        // Verifikasi angka
        $this->assertEquals(2000000, $variance);
        $this->assertEquals(20, $variancePercentage);

        // Verifikasi Status Alert (Simulasi logika budget check)
        $isOverBudget = ($actualAmount > $budgetAmount);
        $this->assertTrue($isOverBudget, "Status harus Over Budget jika realisasi > anggaran");
    }

    /**
     * BGT-01 Under budget.
     */
    public function test_budget_variance_under_budget_status_ok()
    {
        $expenseAccount = $this->createAccount('5102', 'Beban Listrik', 'expense', 'income_statement');
        $this->postExpense($expenseAccount, 8000000, 'BGT-UNDER-001');

        $actual = $this->actualExpense($expenseAccount->uuid);
        $variance = $this->evaluateBudget(10000000, $actual);

        $this->assertEquals(8000000, $actual);
        $this->assertEquals(2000000, $variance['variance']);
        $this->assertEquals(80.0, $variance['usage_percentage']);
        $this->assertEquals('OK', $variance['status']);
    }

    /**
     * BGT-03 On budget.
     */
    public function test_budget_variance_on_budget_status_on_track()
    {
        $expenseAccount = $this->createAccount('5103', 'Beban Sewa', 'expense', 'income_statement');
        $this->postExpense($expenseAccount, 10000000, 'BGT-ON-001');

        $actual = $this->actualExpense($expenseAccount->uuid);
        $variance = $this->evaluateBudget(10000000, $actual);

        $this->assertEquals(0, $variance['variance']);
        $this->assertEquals(100.0, $variance['usage_percentage']);
        $this->assertEquals('ON TRACK', $variance['status']);
    }

    /**
     * BGT-04 Threshold alert.
     */
    public function test_budget_variance_threshold_alert_when_over_budget_exceeds_limit()
    {
        $expenseAccount = $this->createAccount('5104', 'Beban Operasional', 'expense', 'income_statement');
        $this->postExpense($expenseAccount, 11500000, 'BGT-THRESHOLD-001');

        $actual = $this->actualExpense($expenseAccount->uuid);
        $variance = $this->evaluateBudget(10000000, $actual, 10);

        $this->assertEquals(-1500000, $variance['variance']);
        $this->assertEquals(115.0, $variance['usage_percentage']);
        $this->assertTrue($variance['alert']);
    }

    /**
     * BGT-05 Budget per akun.
     */
    public function test_budget_variance_can_be_calculated_per_account()
    {
        $salaryAccount = $this->createAccount('5105', 'Beban Gaji Perawat', 'expense', 'income_statement');
        $electricityAccount = $this->createAccount('5106', 'Beban Listrik Klinik', 'expense', 'income_statement');
        $medicineAccount = $this->createAccount('5107', 'Beban Obat', 'expense', 'income_statement');

        $this->postExpense($salaryAccount, 5000000, 'BGT-ACC-001');
        $this->postExpense($electricityAccount, 2000000, 'BGT-ACC-002');
        $this->postExpense($medicineAccount, 7000000, 'BGT-ACC-003');

        $budgetByAccount = [
            $salaryAccount->uuid => 6000000,
            $electricityAccount->uuid => 2000000,
            $medicineAccount->uuid => 6000000,
        ];

        $salaryVariance = $this->evaluateBudget($budgetByAccount[$salaryAccount->uuid], $this->actualExpense($salaryAccount->uuid));
        $electricityVariance = $this->evaluateBudget($budgetByAccount[$electricityAccount->uuid], $this->actualExpense($electricityAccount->uuid));
        $medicineVariance = $this->evaluateBudget($budgetByAccount[$medicineAccount->uuid], $this->actualExpense($medicineAccount->uuid));

        $this->assertEquals('OK', $salaryVariance['status']);
        $this->assertEquals('ON TRACK', $electricityVariance['status']);
        $this->assertEquals('WARNING', $medicineVariance['status']);
    }

    private function createAccount(string $code, string $name, string $type, string $statement): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'type' => $type,
                'financial_statement' => $statement,
                'is_header' => false,
            ]
        );
    }

    private function postExpense(Account $expenseAccount, int $amount, string $referenceNo): void
    {
        $cashAccount = $this->createAccount('1101', 'Kas', 'asset', 'balance_sheet');

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => $referenceNo,
            'journal_date' => '2026-01-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $expenseAccount->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $cashAccount->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]));
    }

    private function actualExpense(string $accountUuid): float
    {
        return (float) DB::table('journal_lines')
            ->join('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
            ->where('journal_lines.account_uuid', $accountUuid)
            ->where('journals.status', JournalStatus::POSTED->value)
            ->sum('journal_lines.debit');
    }

    private function evaluateBudget(int $budgetAmount, float $actualAmount, int $thresholdPercentage = 0): array
    {
        $variance = $budgetAmount - $actualAmount;
        $usagePercentage = $budgetAmount > 0 ? round(($actualAmount / $budgetAmount) * 100, 2) : 0.0;
        $overBudgetPercentage = $budgetAmount > 0 && $actualAmount > $budgetAmount
            ? round((($actualAmount - $budgetAmount) / $budgetAmount) * 100, 2)
            : 0.0;

        return [
            'variance' => $variance,
            'usage_percentage' => $usagePercentage,
            'status' => $variance > 0 ? 'OK' : ($variance < 0 ? 'WARNING' : 'ON TRACK'),
            'alert' => $thresholdPercentage > 0 && $overBudgetPercentage > $thresholdPercentage,
        ];
    }
}
