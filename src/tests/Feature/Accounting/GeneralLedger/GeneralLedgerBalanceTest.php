<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * GeneralLedgerBalanceTest
 *
 * Menguji akurasi perhitungan saldo berjalan (running balance),
 * reset saldo akun Laba/Rugi di awal tahun, dan konsistensi persamaan akuntansi.
 */
class GeneralLedgerBalanceTest extends TestCase
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

        // Pastikan periode Februari & Maret 2026 tersedia — bypass gap validation seperti DemoSeeder
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-02'],
                ['name' => 'February 2026', 'start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'status' => 'open']
            );
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-03'],
                ['name' => 'March 2026', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open']
            );
        });
    }

    /**
     * Test 1.1: Verifikasi Running Balance per Akun
     *
     * Skenario: 5 transaksi berurutan pada akun Kas (1101),
     * memvalidasi running balance di setiap baris laporan GL.
     */
    public function test_running_balance_accuracy_for_account(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Skenario: 5 transaksi berurutan
        $transactions = [
            ['day' => 1,  'debit' => 100000, 'credit' => 0,      'contra' => $pendapatanAccount, 'desc' => 'Penerimaan kas'],
            ['day' => 5,  'debit' => 0,      'credit' => 30000,  'contra' => $biayaAccount,      'desc' => 'Pembayaran biaya'],
            ['day' => 10, 'debit' => 50000,  'credit' => 0,      'contra' => $pendapatanAccount, 'desc' => 'Penerimaan kas 2'],
            ['day' => 15, 'debit' => 0,      'credit' => 20000,  'contra' => $biayaAccount,      'desc' => 'Pembayaran biaya 2'],
            ['day' => 20, 'debit' => 25000,  'credit' => 0,      'contra' => $pendapatanAccount, 'desc' => 'Penerimaan kas 3'],
        ];

        foreach ($transactions as $i => $tx) {
            $lines = [
                ['account_uuid' => $kasAccount->uuid, 'debit' => $tx['debit'], 'credit' => $tx['credit']],
                ['account_uuid' => $tx['contra']->uuid, 'debit' => $tx['credit'], 'credit' => $tx['debit']],
            ];

            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "BAL-TEST-" . ($i + 1),
                'reference_uuid'   => $i + 100,
                'journal_date'   => "2026-03-" . str_pad($tx['day'], 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => $tx['desc'],
                'lines'          => $lines,
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
            ]));
        }

        // Ambil laporan GL bulan Maret
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // Verifikasi jumlah transaksi
        $this->assertCount(5, $report['transactions'], 'Harus ada 5 transaksi');

        // Verifikasi running balance berurutan
        $expectedMutations = [100000, -30000, 50000, -20000, 25000];
        $balance = $report['opening_balance'];

        foreach ($report['transactions'] as $i => $tx) {
            $balance += $expectedMutations[$i];
            $this->assertEquals(
                $balance,
                $tx['balance'],
                "Running balance salah di transaksi #{$i}: expected {$balance}, got {$tx['balance']}"
            );
        }

        // Verifikasi saldo akhir = opening + total debit - total credit
        $expectedClosing = $report['opening_balance'] + $report['total_debit'] - $report['total_credit'];
        $this->assertEquals($expectedClosing, $report['closing_balance'], 'Saldo akhir tidak konsisten');
    }

    /**
     * Test 1.2: Verifikasi Reset Balance untuk Akun Laba/Rugi per Tahun
     *
     * Skenario: Akun Pendapatan (revenue) harus memiliki opening balance = 0
     * di awal tahun fiskal baru, meskipun ada transaksi di tahun sebelumnya.
     */
    public function test_income_statement_accounts_reset_at_year_start(): void
    {
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Transaksi Pendapatan di Januari 2026
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'REV-JAN-001',
            'reference_uuid'   => 200,
            'journal_date'   => '2026-01-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Pendapatan Jan 2026',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 1000000],
            ],
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
        ]));

        // Ambil opening balance pendapatan untuk tahun yang sama (Maret 2026)
        // Karena masih tahun yang sama, opening harus mengakumulasi mutasi sebelumnya
        $reportSameYear = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $pendapatanAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // Opening balance Maret harus mencakup transaksi Januari (-1,000,000 karena credit di laba/rugi)
        $this->assertNotEquals(0.0, $reportSameYear['opening_balance'],
            'Opening balance akun P&L di tahun yang sama harus mengakumulasi transaksi sebelumnya'
        );

        // Ambil opening balance untuk awal tahun itu sendiri (1 Januari)
        // P&L account: pencarian dimulai dari awal tahun, jadi opening = 0
        $reportYearStart = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $pendapatanAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-01-01',
            'date_to'      => '2026-01-31',
        ]));

        $this->assertEquals(0.0, $reportYearStart['opening_balance'],
            'Opening balance akun P&L di 1 Januari harus 0 (reset tahunan)'
        );
    }

    /**
     * Test 1.3: Verifikasi Konsistensi Persamaan Akuntansi (Aset = Kewajiban + Ekuitas)
     *
     * Skenario: Setelah serangkaian transaksi balance, verifikasi bahwa
     * total debit == total credit di seluruh GL menggunakan checkLedgerBalance.
     */
    public function test_accounting_equation_via_trial_balance(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Post beberapa jurnal balanced
        for ($i = 1; $i <= 3; $i++) {
            $amount = $i * 100000;
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "EQ-TEST-{$i}",
                'reference_uuid'   => 300 + $i,
                'journal_date'   => "2026-03-" . str_pad($i * 5, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Transaksi persamaan akuntansi #{$i}",
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => $amount, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $amount],
                ],
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
            ]));
        }

        // Sync dan cek trial balance
        $period = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();

        $this->glService->syncByPeriod($period->uuid);

        $result = $this->glService->checkLedgerBalance($period->uuid);

        $this->assertTrue($result['is_balanced'], "Trial Balance tidak seimbang: " .
            "Debit={$result['total_debit']}, Credit={$result['total_credit']}, Diff={$result['difference']}"
        );
        $this->assertLessThan(0.01, $result['difference']);
    }

    /**
     * Test 1.4: Verifikasi Total Debit dan Credit pada Laporan GL
     *
     * Skenario: Posting 5 jurnal, kemudian memastikan total_debit dan total_credit
     * pada laporan GL konsisten dengan jumlah baris yang masuk.
     */
    public function test_debit_credit_totals_consistency(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        $amounts = [150000, 200000, 75000];
        $totalPosted = 0;

        foreach ($amounts as $i => $amount) {
            $totalPosted += $amount;
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "DC-TEST-" . ($i + 1),
                'reference_uuid'   => 400 + $i,
                'journal_date'   => "2026-03-" . str_pad(($i + 1) * 3, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Biaya operasional #{$i}",
                'lines'          => [
                    ['account_uuid' => $biayaAccount->uuid, 'debit' => $amount, 'credit' => 0],
                    ['account_uuid' => $kasAccount->uuid, 'debit' => 0, 'credit' => $amount],
                ],
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
            ]));
        }

        // Laporan GL akun Biaya (hanya debit)
        $reportBiaya = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $biayaAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        $this->assertEquals($totalPosted, $reportBiaya['total_debit'], 'Total debit biaya tidak sesuai');
        $this->assertEquals(0, $reportBiaya['total_credit'], 'Total credit biaya harus 0');

        // Laporan GL akun Kas (hanya credit untuk transaksi ini)
        $reportKas = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        $this->assertGreaterThanOrEqual($totalPosted, $reportKas['total_credit'],
            'Total credit kas harus mencakup semua pembayaran biaya'
        );
    }
}

