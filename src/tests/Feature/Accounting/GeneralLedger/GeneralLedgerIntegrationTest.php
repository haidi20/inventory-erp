<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GeneralLedgerIntegrationTest
 *
 * Menguji skenario end-to-end: proses tutup bulan lengkap,
 * void jurnal, dan integritas data setelah rollback.
 */
class GeneralLedgerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected GeneralLedgerService $glService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01');
        $this->seed(DemoSeeder::class);
        Carbon::setTestNow('2026-03-24');

        $this->journalService = app(JournalService::class);
        $this->glService = app(GeneralLedgerService::class);
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

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
     * Test 5.1: End-to-End Monthly Closing Process
     *
     * Skenario: Posting jurnal harian selama sebulan, sync GL,
     * verifikasi trial balance, tutup periode, lalu verifikasi
     * posting ke periode tertutup ditolak.
     */
    public function test_end_to_end_monthly_closing(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Step 1: Posting 10 jurnal harian (simulasi aktivitas bulanan)
        $totalRevenue = 0;
        $totalExpense = 0;

        for ($day = 1; $day <= 10; $day++) {
            $revenue = ($day * 50000);
            $expense = ($day * 20000);
            $totalRevenue += $revenue;
            $totalExpense += $expense;

            // Revenue entry
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "E2E-REV-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_uuid'   => 7000 + $day,
                'journal_date'   => "2026-03-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Pendapatan harian #{$day}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => $revenue, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $revenue],
                ],
            ]));

            // Expense entry
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "E2E-EXP-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_uuid'   => 7100 + $day,
                'journal_date'   => "2026-03-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Biaya harian #{$day}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $biayaAccount->uuid, 'debit' => $expense, 'credit' => 0],
                    ['account_uuid' => $kasAccount->uuid, 'debit' => 0, 'credit' => $expense],
                ],
            ]));
        }

        // Step 2: Sync GL
        $period = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();

        $this->glService->syncByPeriod($period->uuid);

        // Step 3: Verifikasi Trial Balance sebelum tutup
        $trialBalance = $this->glService->checkLedgerBalance($period->uuid);
        $this->assertTrue($trialBalance['is_balanced'],
            "Trial Balance pra-closing harus balanced"
        );

        // Step 4: Tutup Periode
        $period->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        $this->assertEquals('closed', $period->fresh()->status);

        // Step 5: Verifikasi posting ke periode tertutup GAGAL
        $this->expectException(Exception::class);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'E2E-FAIL-001',
            'reference_uuid'   => 7999,
            'journal_date'   => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Tidak boleh posting ke periode tertutup',
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 100000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 100000],
            ],
        ]));
    }

    /**
     * Test 5.2: Void Journal Reversal Verification
     *
     * Skenario: Posting jurnal, void-kan, kemudian verifikasi
     * bahwa balance kembali ke keadaan semula.
     */
    public function test_void_journal_reversal(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Ambil balance sebelum posting
        $reportBefore = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));
        $closingBefore = $reportBefore['closing_balance'];

        // Posting jurnal
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $branchId,
            'reference_no'   => 'VOID-TEST-001',
            'reference_uuid'   => 8000,
            'journal_date'   => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Jurnal yang akan di-void',
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => 500000, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 500000],
            ],
        ]));

        // Verifikasi balance meningkat setelah posting
        $reportAfterPost = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));
        $this->assertEquals(
            $closingBefore + 500000,
            $reportAfterPost['closing_balance'],
            'Closing balance harus naik 500k setelah posting'
        );

        // Void jurnal
        $this->journalService->voidJournal($journal->uuid);

        // Verifikasi balance kembali ke semula (jurnal asli + reversal = 0)
        $reportAfterVoid = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        $this->assertEquals(
            $closingBefore,
            $reportAfterVoid['closing_balance'],
            'Closing balance harus kembali ke semula setelah void'
        );
    }

    /**
     * Test 5.3: Data Integrity After Failed Transaction (Rollback)
     *
     * Skenario: Posting jurnal yang gagal (unbalanced) tidak boleh
     * mengubah data GL sama sekali.
     */
    public function test_data_integrity_after_rollback(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Snapshot awal
        $journalCountBefore = Journal::where('branch_uuid', $branchId)->count();

        $reportBefore = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));
        $closingBefore = $reportBefore['closing_balance'];

        // Coba posting jurnal UNBALANCED (100k debit vs 50k credit)
        try {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => 'FAIL-ROLLBACK-001',
                'reference_uuid'   => 9000,
                'journal_date'   => '2026-03-20',
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => 'Jurnal unbalanced: harus gagal',
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => 100000, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 50000],
                ],
            ]));
            $this->fail('Seharusnya Exception dilempar untuk jurnal unbalanced');
        } catch (Exception $e) {
            // Expected exception: Journal unbalanced
            $this->assertStringContainsString('unbalanced', strtolower($e->getMessage()));
        }

        // Verifikasi: jumlah jurnal tidak bertambah
        $journalCountAfter = Journal::where('branch_uuid', $branchId)->count();
        $this->assertEquals($journalCountBefore, $journalCountAfter,
            'Jurnal tidak boleh tersimpan setelah gagal (rollback)'
        );

        // Verifikasi: balance tidak berubah
        $reportAfter = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));
        $this->assertEquals($closingBefore, $reportAfter['closing_balance'],
            'Closing balance tidak boleh berubah setelah rollback'
        );
    }

    /**
     * Test 5.4: Multiple Periods Sequential Posting
     *
     * Skenario: Posting di Jan, Feb, Mar secara berurutan,
     * verifikasi saldo akhir tiap periode menjadi saldo awal periode berikutnya.
     */
    public function test_sequential_periods_carry_forward(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        $periods = [
            ['period' => '2026-01', 'start' => '2026-01-01', 'end' => '2026-01-31', 'amount' => 100000],
            ['period' => '2026-02', 'start' => '2026-02-01', 'end' => '2026-02-28', 'amount' => 200000],
            ['period' => '2026-03', 'start' => '2026-03-01', 'end' => '2026-03-31', 'amount' => 300000],
        ];

        $prevClosing = null;

        foreach ($periods as $i => $p) {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "SEQ-" . str_replace('-', '', $p['period']),
                'reference_uuid'   => 9100 + $i,
                'journal_date'   => $p['start'], // Posting di awal bulan
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Sequential carry-forward #{$i}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => $p['amount'], 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $p['amount']],
                ],
            ]));

            $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
                'account_uuid' => $kasAccount->uuid,
                'branch_uuid'  => $branchId,
                'date_from'    => $p['start'],
                'date_to'      => $p['end'],
            ]));

            if ($prevClosing !== null) {
                $this->assertEquals($prevClosing, $report['opening_balance'],
                    "Opening balance {$p['period']} harus = closing balance periode sebelumnya"
                );
            }

            $prevClosing = $report['closing_balance'];
        }
    }
}

