<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * GeneralLedgerPerformanceTest
 *
 * Menguji performa dan skalabilitas Stored Procedure serta
 * query laporan GL pada dataset besar.
 */
class GeneralLedgerPerformanceTest extends TestCase
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
     * Test 3.1: Bulk Journal Processing via Stored Procedure
     *
     * Skenario: Posting 100 jurnal secara berurutan,
     * kemudian sync via Stored Procedure dan ukur waktu eksekusi.
     */
    public function test_bulk_journal_processing_performance(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Posting 100 jurnal
        for ($i = 1; $i <= 100; $i++) {
            $day = (($i - 1) % 28) + 1;
            $amount = rand(10000, 500000);

            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "PERF-BULK-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'reference_uuid'   => 1000 + $i,
                'journal_date'   => "2026-03-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Bulk performance test #{$i}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => $amount, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $amount],
                ],
            ]));
        }

        // Ukur waktu sync SP
        $period = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();

        $startTime = microtime(true);
        $this->glService->syncByPeriod($period->uuid);
        $executionTime = microtime(true) - $startTime;

        // Verifikasi GL records terbentuk
        $glCount = GeneralLedger::where('branch_uuid', $branchId)
            ->whereBetween('journal_date', [$period->start_date, $period->end_date])
            ->count();

        $this->assertGreaterThan(0, $glCount, 'Stored Procedure harus menghasilkan record GL');

        // Performa: sync harus selesai dalam 10 detik
        $this->assertLessThan(10, $executionTime,
            "Sync 100 jurnal menghabiskan {$executionTime} detik, melebihi threshold 10 detik"
        );

        Log::info("Performance: Bulk sync 100 journals → {$glCount} GL records in {$executionTime}s");
    }

    /**
     * Test 3.2: Query Performance pada Dataset Besar
     *
     * Skenario: Setelah 100 jurnal di-post dan di-sync,
     * ukur performa query getLedgerReport.
     */
    public function test_large_dataset_query_performance(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $branchId = $this->branch->uuid;

        // Posting 50 jurnal (minimum untuk test performa query)
        for ($i = 1; $i <= 50; $i++) {
            $day = (($i - 1) % 28) + 1;
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "PERF-QRY-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'reference_uuid'   => 2000 + $i,
                'journal_date'   => "2026-03-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "Query perf test #{$i}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => 100000, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => 100000],
                ],
            ]));
        }

        // Ukur waktu query laporan GL
        $startTime = microtime(true);
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));
        $executionTime = microtime(true) - $startTime;

        // Verifikasi hasil
        $this->assertGreaterThanOrEqual(50, count($report['transactions']),
            'Laporan harus memuat minimal 50 transaksi'
        );

        // Performa: query harus selesai dalam 3 detik
        $this->assertLessThan(3, $executionTime,
            "Query laporan GL menghabiskan {$executionTime} detik, melebihi threshold 3 detik"
        );

        Log::info("Performance: getLedgerReport (50+ txns) in {$executionTime}s");
    }

    /**
     * Test 3.3: Stored Procedure Data Integrity
     *
     * Skenario: Verifikasi bahwa data GL dari Stored Procedure
     * menghasilkan running balance yang benar.
     */
    public function test_stored_procedure_data_integrity(): void
    {
        $kasAccount = Account::where('code', '1101')->firstOrFail();
        $pendapatanAccount = Account::where('code', '4101')->firstOrFail();
        $biayaAccount = Account::where('code', '5201')->firstOrFail();
        $branchId = $this->branch->uuid;

        $amounts = [100000, 200000, 150000, 50000, 300000];

        foreach ($amounts as $i => $amount) {
            $day = ($i + 1) * 5;
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'      => $branchId,
                'reference_no'   => "SP-INT-" . ($i + 1),
                'reference_uuid'   => 3000 + $i,
                'journal_date'   => "2026-03-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                'reference_type' => JournalReferenceType::GENERAL,
                'description'    => "SP integrity test #{$i}",
                'status'         => JournalStatus::POSTED,
                'workflow_status'=> JournalWorkflowStatus::APPROVED,
                'lines'          => [
                    ['account_uuid' => $kasAccount->uuid, 'debit' => $amount, 'credit' => 0],
                    ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $amount],
                ],
            ]));
        }

        // Sync via SP
        $period = ClosingPeriod::where('branch_uuid', $branchId)
            ->where('period', '2026-03')
            ->firstOrFail();

        $this->glService->syncByPeriod($period->uuid);

        // Verifikasi lewat getLedgerReport
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $kasAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // Verifikasi: closing = opening + total_debit - total_credit
        $expectedClosing = $report['opening_balance'] + $report['total_debit'] - $report['total_credit'];
        $this->assertEquals($expectedClosing, $report['closing_balance'],
            'SP harus menghasilkan closing balance yang konsisten'
        );

        // Verifikasi: running balance monoton naik (karena semua debit)
        $prevBalance = $report['opening_balance'];
        foreach ($report['transactions'] as $tx) {
            $this->assertGreaterThanOrEqual($prevBalance, $tx['balance'],
                'Running balance harus naik untuk transaksi debit-only'
            );
            $prevBalance = $tx['balance'];
        }
    }
}

