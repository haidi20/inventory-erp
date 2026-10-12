<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrialBalanceSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected TrialBalanceService $trialBalanceService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->trialBalanceService = app(TrialBalanceService::class);

        $this->branch = Branch::create([
            'name' => 'Sim Branch', 'short_name' => 'SIM', 'is_active' => true
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Skenario 1.1: Konsistensi Data Multi-Sumber
     * Memastikan data GeneralLedger konsisten dengan AccountBalances setelah sinkronisasi.
     */
    public function test_consistency_general_ledger_vs_account_balances()
    {
        // 1. Setup Period
        $period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        // 2. Setup Accounts (mimic TrialBalanceIntegrationTest pattern)
        $accCash = Account::create([
            'code' => '1101', 'name' => 'Kas', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $accRevenue = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);

        // 3. Post via JournalEntity (proven pattern)
        $amount = 1000000;
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $this->branch->uuid,
            'reference_no'   => 'SIM-CONS-001',
            'reference_uuid'   => 0,
            'journal_date'   => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Test Konsistensi',
            'lines'          => [
                ['account_uuid' => $accCash->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $accRevenue->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]));

        // 4. Sync GL
        app(GeneralLedgerService::class)->syncByPeriod($period->uuid);

        // 5. Compare GL total vs AccountBalance
        $glTotalDebit = GeneralLedger::where('branch_uuid', $this->branch->uuid)
            ->where('account_uuid', $accCash->uuid)
            ->sum('debit');

        $abBalance = AccountBalance::where('branch_uuid', $this->branch->uuid)
            ->where('account_uuid', $accCash->uuid)
            ->where('period', '2026-03')
            ->first();

        $this->assertNotNull($abBalance, 'AccountBalance harus ada setelah sync');
        $this->assertEquals(
            (float) $glTotalDebit,
            (float) $abBalance->total_debit,
            "Total debit GL harus sama dengan total_debit AccountBalance"
        );
    }

    /**
     * Skenario 4.1: Deteksi Anomali - Akun Header Memiliki Saldo
     * Header account seharusnya tidak punya transaksi langsung.
     */
    public function test_anomaly_header_account_balance()
    {
        $period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        // Header and child
        $header = Account::create([
            'code' => '1000', 'name' => 'AKTIVA LANCAR', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 1
        ]);
        Account::create([
            'code' => '1001', 'name' => 'Kas Kecil', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0, 'parent_uuid' => $header->uuid
        ]);

        // Force anomalous transaction to header via raw GL + dummy journal
        $journal = Journal::create([
            'branch_uuid' => $this->branch->uuid, 'transaction_code' => 'DUM/ANM/001',
            'reference_no' => 'REF-ANM-001', 'reference_uuid' => 0,
            'journal_date' => '2026-03-15', 'status' => 'posted'
        ]);
        $line = JournalLine::create([
            'branch_uuid' => $this->branch->uuid, 'journal_uuid' => $journal->uuid,
            'account_uuid' => $header->uuid, 'debit' => 500000, 'credit' => 0
        ]);
        GeneralLedger::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $header->uuid,
            'journal_uuid' => $journal->uuid, 'journal_line_uuid' => $line->uuid,
            'journal_date' => '2026-03-15', 'reference_no' => 'ANM-001',
            'debit' => 500000, 'credit' => 0, 'balance' => 500000
        ]);

        // Detect anomaly: find header accounts with GL entries
        $anomalies = GeneralLedger::where('branch_uuid', $this->branch->uuid)
            ->whereHas('account', fn($q) => $q->where('is_header', true))
            ->get();

        $this->assertNotEmpty($anomalies, 'Harus mendeteksi anomali pada akun header');
        $this->assertTrue(
            $anomalies->first()->account->is_header,
            'Akun yang terdeteksi harus bertipe header'
        );
    }

    /**
     * Skenario 4.2: Deteksi Saldo Negatif Tidak Wajar pada Akun Aset
     */
    public function test_anomaly_negative_asset_balance()
    {
        $period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $accCash = Account::create([
            'code' => '1101', 'name' => 'Kas', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);

        // Force negative balance via raw GL + dummy journal
        $journal = Journal::create([
            'branch_uuid' => $this->branch->uuid, 'transaction_code' => 'DUM/NEG/001',
            'reference_no' => 'REF-NEG-001', 'reference_uuid' => 0,
            'journal_date' => '2026-03-20', 'status' => 'posted'
        ]);
        $line = JournalLine::create([
            'branch_uuid' => $this->branch->uuid, 'journal_uuid' => $journal->uuid,
            'account_uuid' => $accCash->uuid, 'debit' => 0, 'credit' => 500000
        ]);
        GeneralLedger::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $accCash->uuid,
            'journal_uuid' => $journal->uuid, 'journal_line_uuid' => $line->uuid,
            'journal_date' => '2026-03-20', 'reference_no' => 'NEG-001',
            'debit' => 0, 'credit' => 500000, 'balance' => -500000
        ]);

        // Insert AccountBalance with negative closing
        AccountBalance::create([
            'branch_uuid' => $this->branch->uuid, 'account_uuid' => $accCash->uuid,
            'period' => '2026-03', 'opening_balance' => 0,
            'total_debit' => 0, 'total_credit' => 500000, 'closing_balance' => -500000
        ]);

        // Detect: asset accounts with negative closing_balance
        $negatives = AccountBalance::where('branch_uuid', $this->branch->uuid)
            ->where('closing_balance', '<', 0)
            ->whereHas('account', fn($q) => $q->where('type', 'asset'))
            ->get();

        $this->assertNotEmpty($negatives, 'Harus mendeteksi saldo negatif pada akun aset');
        $this->assertTrue(
            $negatives->first()->closing_balance < 0,
            'Closing balance harus negatif'
        );
    }
}
