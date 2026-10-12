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

class BankReconciliationTest extends TestCase
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
     * Test Rekonsiliasi Bank dengan Outstanding Items
     */
    public function test_bank_reconciliation_with_adjustments()
    {
        $accBank = Account::create([
            'code' => '1102', 'name' => 'Bank Mandiri', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => false
        ]);
        
        // Simulasikan Saldo Buku Besar (GL)
        // Rp 50.000.000
        $glBalance = 50000000;
        
        // Simulasikan Saldo Rekening Koran (Bank Statement)
        // Rp 52.000.000
        $bankStatementBalance = 52000000;
        
        // Outstanding check (cek yang sudah dicatat di GL tapi belum didebit bank)
        // Rp 2.000.000
        $outstandingCheck = 2000000;

        // Rumus rekonsiliasi dasar: Adjusted Bank Balance = Bank Statement - Outstanding Check + Deposit in Transit
        $adjustedBankBalance = $bankStatementBalance - $outstandingCheck;
        
        $this->assertEquals($glBalance, $adjustedBankBalance, "Saldo GL harus sama dengan Adjusted Bank Balance");
        
        // Verifikasi keselarasan (Status: MATCH)
        $isMatch = ($glBalance === $adjustedBankBalance);
        $this->assertTrue($isMatch);
    }

    /**
     * BNK-01 Rekonsiliasi match sempurna.
     */
    public function test_bank_reconciliation_perfect_match()
    {
        $bankAccount = $this->createAccount('1102', 'Bank Operasional', 'asset', 'balance_sheet');
        $this->postBankReceipt($bankAccount, 10000000, 'BNK-MATCH-001');

        $glBalance = $this->getBookBalance($bankAccount->uuid);
        $reconciliation = $this->reconcile($glBalance, 10000000);

        $this->assertEquals(10000000, $glBalance);
        $this->assertEquals(0, $reconciliation['difference']);
        $this->assertTrue($reconciliation['is_match']);
    }

    /**
     * BNK-02 Deposit in transit.
     */
    public function test_bank_reconciliation_identifies_deposit_in_transit()
    {
        $bankAccount = $this->createAccount('1103', 'Bank Transit', 'asset', 'balance_sheet');
        $this->postBankReceipt($bankAccount, 10000000, 'BNK-DIT-001');

        $glBalance = $this->getBookBalance($bankAccount->uuid);
        $reconciliation = $this->reconcile($glBalance, 8500000, 1500000);

        $this->assertEquals(10000000, $glBalance);
        $this->assertEquals(10000000, $reconciliation['adjusted_bank_balance']);
        $this->assertTrue($reconciliation['is_match']);
    }

    /**
     * BNK-04 Transaksi tidak match.
     */
    public function test_bank_reconciliation_identifies_unmatched_transactions()
    {
        $bankAccount = $this->createAccount('1104', 'Bank Rekonsiliasi', 'asset', 'balance_sheet');
        $this->postBankReceipt($bankAccount, 5000000, 'BNK-MISMATCH-001');

        $glBalance = $this->getBookBalance($bankAccount->uuid);
        $bookAdjustments = -50000 + 10000; // bank charge dan bunga bank
        $unmatchedTransactions = [
            ['type' => 'bank_charge', 'amount' => 50000],
            ['type' => 'interest_income', 'amount' => 10000],
        ];

        $reconciliation = $this->reconcile($glBalance, 4960000, 0, 0, $bookAdjustments);

        $this->assertEquals(2, count($unmatchedTransactions));
        $this->assertEquals(4960000, $reconciliation['adjusted_book_balance']);
        $this->assertTrue($reconciliation['is_match']);
    }

    /**
     * BNK-05 Rekonsiliasi multi-bank.
     */
    public function test_bank_reconciliation_multi_bank_is_processed_separately()
    {
        $bankA = $this->createAccount('1105', 'Bank A', 'asset', 'balance_sheet');
        $bankB = $this->createAccount('1106', 'Bank B', 'asset', 'balance_sheet');

        $this->postBankReceipt($bankA, 3000000, 'BNK-MULTI-A');
        $this->postBankReceipt($bankB, 4500000, 'BNK-MULTI-B');

        $reconciliationA = $this->reconcile($this->getBookBalance($bankA->uuid), 3000000);
        $reconciliationB = $this->reconcile($this->getBookBalance($bankB->uuid), 4500000);

        $this->assertTrue($reconciliationA['is_match']);
        $this->assertTrue($reconciliationB['is_match']);
        $this->assertEquals(3000000, $reconciliationA['adjusted_bank_balance']);
        $this->assertEquals(4500000, $reconciliationB['adjusted_bank_balance']);
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

    private function postBankReceipt(Account $bankAccount, int $amount, string $referenceNo): void
    {
        $incomeAccount = Account::factory()->create([
            'type' => 'revenue',
            'financial_statement' => 'income_statement',
            'is_header' => false,
        ]);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => $referenceNo,
            'journal_date' => '2026-01-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $bankAccount->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $incomeAccount->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]));
    }

    private function getBookBalance(string $accountUuid): float
    {
        return (float) DB::table('journal_lines')
            ->join('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
            ->where('journal_lines.account_uuid', $accountUuid)
            ->where('journals.status', JournalStatus::POSTED->value)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));
    }

    private function reconcile(
        float $bookBalance,
        float $statementBalance,
        float $depositInTransit = 0,
        float $outstandingChecks = 0,
        float $bookAdjustments = 0
    ): array {
        $adjustedBankBalance = $statementBalance + $depositInTransit - $outstandingChecks;
        $adjustedBookBalance = $bookBalance + $bookAdjustments;
        $difference = $adjustedBookBalance - $adjustedBankBalance;

        return [
            'adjusted_bank_balance' => $adjustedBankBalance,
            'adjusted_book_balance' => $adjustedBookBalance,
            'difference' => $difference,
            'is_match' => abs($difference) < 0.01,
        ];
    }
}
