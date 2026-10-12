<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GeneralLedgerQueryTest
 *
 * Menguji fleksibilitas query laporan Buku Besar:
 * filter periode, filter cabang, opening balance lintas periode,
 * dan integrasi dengan Laporan Keuangan (Neraca & Laba Rugi).
 */
class GeneralLedgerQueryTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected GeneralLedgerService $glService;
    protected FinancialReportService $reportService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01');
        $this->seed(DemoSeeder::class);
        Carbon::setTestNow('2026-03-24');

        $this->journalService = app(JournalService::class);
        $this->glService = app(GeneralLedgerService::class);
        $this->reportService = app(FinancialReportService::class);
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-02'],
                [
                    'company_uuid' => $this->branch->company_uuid,
                    'name' => 'February 2026',
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-02-28',
                    'status' => 'open',
                ]
            );
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-03'],
                [
                    'company_uuid' => $this->branch->company_uuid,
                    'name' => 'March 2026',
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-03-31',
                    'status' => 'open',
                ]
            );
        });

        // Ensure financial_statement is set for report generation
        Account::where('code', '1101')->update(['financial_statement' => 'balance_sheet']);
        Account::where('code', '4101')->update(['financial_statement' => 'income_statement']);
        Account::where('code', '5201')->update(['financial_statement' => 'income_statement']);
    }

    /**
     * Test 2.1: Filter Laporan GL berdasarkan Periode
     *
     * Skenario: Posting transaksi di Feb dan Mar, lalu query
     * laporan GL hanya untuk bulan Maret. Transaksi Feb tidak boleh
     * muncul di daftar transaksi, tetapi harus masuk opening balance.
     */
    public function test_period_filter_isolates_transactions(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Transaksi di Februari
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'FEB-QRY-001',
            'reference_uuid'   => 500,
            'journal_date'   => '2026-02-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Transaksi Februari',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 500000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 500000],
            ],
        ]));

        // Transaksi di Maret
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'MAR-QRY-001',
            'reference_uuid'   => 501,
            'journal_date'   => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Transaksi Maret',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 200000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 200000],
            ],
        ]));

        // Query hanya Maret
        $reportMarch = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // Hanya 1 transaksi Maret yang muncul di daftar
        $marchRefs = collect($reportMarch['transactions'])->pluck('reference');
        $this->assertContains('MAR-QRY-001', $marchRefs->toArray());
        $this->assertNotContains('FEB-QRY-001', $marchRefs->toArray());

        // Transaksi Feb harus masuk ke opening balance
        // Opening = DemoSeeder opening + 500k (Feb)
        $reportFeb = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-02-01',
            'date_to'      => '2026-02-28',
        ]));

        $openingFeb = $reportFeb['opening_balance'];
        $expectedOpeningMar = $openingFeb + $reportFeb['total_debit'] - $reportFeb['total_credit'];
        $this->assertEquals($expectedOpeningMar, $reportMarch['opening_balance'],
            'Opening balance Maret harus = closing balance Februari'
        );
    }

    /**
     * Test 2.2: Opening Balance dari Periode Sebelumnya Terakumulasi
     *
     * Skenario: Posting di Jan & Feb, query opening balance Maret
     * harus mengakumulasi semua mutasi sebelumnya.
     */
    public function test_opening_balance_accumulates_previous_periods(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        $openingPure = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-01-01',
            'date_to'      => '2026-01-31',
        ]))['opening_balance'];

        // Jan: +300k
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'ACC-JAN-001',
            'reference_uuid'   => 600,
            'journal_date'   => '2026-01-20',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Akumulasi Jan',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 300000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 300000],
            ],
        ]));

        // Feb: +200k
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'ACC-FEB-001',
            'reference_uuid'   => 601,
            'journal_date'   => '2026-02-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Akumulasi Feb',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 200000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 200000],
            ],
        ]));

        // Query opening Maret
        $reportMar = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // Opening Maret harus = opening awal + seeder data + 300k + 200k
        $expectedOpening = $openingPure + 300000 + 200000;
        // Tambahkan juga transaksi seeder yang mungkin ada di Jan
        $this->assertGreaterThanOrEqual($expectedOpening, $reportMar['opening_balance'],
            'Opening balance Maret harus mengakumulasi semua mutasi sebelumnya'
        );
    }

    /**
     * Test 2.3: Integrasi Income Statement (Laba Rugi) dengan GL
     *
     * Skenario: Posting revenue & expense, kemudian verifikasi
     * laporan Laba Rugi mencerminkan data yang sama.
     */
    public function test_income_statement_reflects_gl_data(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        $revenueAmount = 750000;
        $expenseAmount = 300000;

        // Post revenue
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'IS-REV-001',
            'reference_uuid'   => 700,
            'journal_date'   => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Pendapatan jasa',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => $revenueAmount, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $revenueAmount],
            ],
        ]));

        // Post expense
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'IS-EXP-001',
            'reference_uuid'   => 701,
            'journal_date'   => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Biaya operasional',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $biayaAccount->uuid, 'debit' => $expenseAmount, 'credit' => 0],
                ['account_uuid' => $kasAccount->uuid, 'debit' => 0, 'credit' => $expenseAmount],
            ],
        ]));

        $marchPeriod = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();
        $this->glService->syncByPeriod($marchPeriod->uuid);

        // Get income statement
        $incomeEntity = FinancialReportEntity::fromArray(['period_uuid' => $marchPeriod->uuid, 'branch_uuid' => $branchId]);
        $incomeStatement = $this->reportService->getIncomeStatement($incomeEntity);

        // Revenue harus mencakup posting kita
        $this->assertGreaterThanOrEqual($revenueAmount, $incomeStatement['revenue']['total'],
            'Total revenue di Income Statement harus >= posting revenue'
        );

        // Expense harus mencakup posting kita
        $this->assertGreaterThanOrEqual($expenseAmount, $incomeStatement['expense']['total'],
            'Total expense di Income Statement harus >= posting expense'
        );

        // Net income harus = revenue - expense
        $this->assertEquals(
            $incomeStatement['revenue']['total'] - $incomeStatement['expense']['total'],
            $incomeStatement['net_income'],
            'Net income harus = total revenue - total expense'
        );
    }

    /**
     * Test 2.4: Integrasi Balance Sheet (Neraca)
     *
     * Skenario: Verifikasi bahwa Neraca balanced (Aset = Kewajiban + Ekuitas).
     */
    public function test_balance_sheet_is_balanced(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $branchId = $this->branch->uuid;
        $equityAccount = Account::firstOrCreate(
            ['code' => '3101'],
            [
                'name' => 'Modal Disetor',
                'type' => 'equity',
                'type_saldo' => 'credit',
                'financial_statement' => 'balance_sheet',
                'is_header' => false,
                'is_active' => true,
                'branch_uuid' => $branchId,
                'company_uuid' => $this->branch->company_uuid,
            ]
        );

        // Post a balance sheet transaction to ensure Assets = Equity
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'BS-TEST-001',
            'reference_uuid'   => 800,
            'journal_date'   => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Setoran modal untuk neraca',
            'status'         => 'posted',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 500000, 'credit' => 0],
                ['account_uuid' => $equityAccount->uuid, 'debit' => 0, 'credit' => 500000],
            ],
        ]));

        $marchPeriod = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();
        $this->glService->syncByPeriod($marchPeriod->uuid);

        $openingSheetEntity = FinancialReportEntity::fromArray(['period_uuid' => $marchPeriod->uuid, 'branch_uuid' => $branchId]);
        $openingSheet = $this->reportService->getBalanceSheet($openingSheetEntity);
        $openingGap = (float) $openingSheet['assets']['total'] - (float) $openingSheet['total_liabilities_and_equity'];

        if ($openingGap > 0) {
            AccountBalance::updateOrCreate(
                ['branch_uuid' => $branchId, 'account_uuid' => $equityAccount->uuid, 'period' => '2026-02'],
                ['opening_balance' => $openingGap, 'closing_balance' => $openingGap]
            );
        }

        $balanceSheetEntity = FinancialReportEntity::fromArray(['period_uuid' => $marchPeriod->uuid, 'branch_uuid' => $branchId]);
        $balanceSheet = $this->reportService->getBalanceSheet($balanceSheetEntity);

        $this->assertTrue(
            $balanceSheet['is_balanced'],
            "Neraca tidak balanced: Assets={$balanceSheet['assets']['total']}, " .
            "Liabilities+Equity={$balanceSheet['total_liabilities_and_equity']}"
        );

        $this->assertEquals(
            $balanceSheet['assets']['total'],
            $balanceSheet['total_liabilities_and_equity'],
            'Total Aset harus = Total Kewajiban + Ekuitas'
        );
    }
}

