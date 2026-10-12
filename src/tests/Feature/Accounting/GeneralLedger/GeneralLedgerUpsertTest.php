<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Support\Str;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeneralLedgerUpsertTest extends TestCase
{
    use RefreshDatabase;

    protected GeneralLedgerService $glService;
    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        $this->glService = app(GeneralLedgerService::class);
        $this->journalService = app(JournalService::class);
        
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $this->cashAccount = Account::where('code', '1101')->firstOrFail();
        $this->revenueAccount = Account::where('code', '4101')->firstOrFail();
    }

    /**
     * Test Skenario: Update Jurnal Terlacak di GL (Konsep Upsert)
     */
    public function test_gl_sync_handles_upsert_on_update(): void
    {
        // 1. Dapatkan periode (Gunakan yang ada dari seeder)
        $period = ClosingPeriod::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('status', 'open')
            ->orderBy('period', 'desc')
            ->first();
        
        if (!$period) {
            $this->fail("No open closing period found in database");
        }

        $testDate = $period->start_date;

        // 2. Buat Jurnal Awal
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REF-UPSERT-001',
            'journal_date' => $testDate,
            'description' => 'Deskripsi Awal',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 1000],
            ]
        ]));

        // 3. Sync ke GL
        $this->glService->syncByPeriod($period->uuid);
        
        $glEntry = GeneralLedger::where('journal_uuid', $journal->uuid)
            ->where('account_uuid', $this->cashAccount->uuid)
            ->firstOrFail();
        
        $this->assertEquals('Deskripsi Awal', $glEntry->description);
        $glIdBefore = $glEntry->uuid;

        // 4. Update Deskripsi di Tabel Journal Secara Langsung (Meniru update di database)
        Journal::where('uuid', $journal->uuid)->update(['description' => 'Deskripsi Terupdate']);

        // 5. Sync Ulang
        $this->glService->syncByPeriod($period->uuid);

        // 6. Verifikasi: Row di GL harus terupdate, bukan bertambah
        $glEntryAfter = GeneralLedger::where('journal_uuid', $journal->uuid)
            ->where('account_uuid', $this->cashAccount->uuid)
            ->first();

        $this->assertEquals('Deskripsi Terupdate', $glEntryAfter->description);
        $this->assertEquals($glIdBefore, $glEntryAfter->uuid, "ID GL tidak boleh berubah (harus UPSERT)");
        
        // Pastikan tidak ada duplikasi
        $this->assertEquals(1, GeneralLedger::where('journal_uuid', $journal->uuid)->where('account_uuid', $this->cashAccount->uuid)->count());
    }

    /**
     * Test Skenario: Cleanup Stale (Hapus GL jika Jurnal Dihapus)
     */
    public function test_gl_sync_cleans_up_stale_entries(): void
    {
        // 1. Dapatkan periode
        $period = ClosingPeriod::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('status', 'open')
            ->orderBy('period', 'desc')
            ->first();
        
        if (!$period) {
            $this->fail("No open closing period found in database");
        }

        $testDate = $period->start_date;

        // 2. Buat Jurnal
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REF-DEL-001',
            'journal_date' => $testDate,
            'description' => 'Akan Dihapus',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 500, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 500],
            ]
        ]));

        $this->glService->syncByPeriod($period->uuid);
        $this->assertTrue(GeneralLedger::where('journal_uuid', $journal->uuid)->exists());

        // 3. Hapus Jurnal (Lewat Service agar Audit Trail/Soft Delete jalan)
        $this->journalService->deleteJournal($journal->uuid);

        // 4. Sync Ulang
        $this->glService->syncByPeriod($period->uuid);

        // 5. Verifikasi: Row di GL harus hilang
        $this->assertFalse(GeneralLedger::where('journal_uuid', $journal->uuid)->exists(), "Row GL harus dihapus jika jurnal asalnya dihapus");
    }

    /**
     * Test Skenario: Add New Journal
     */
    public function test_gl_sync_adds_new_entries(): void
    {
        // 1. Dapatkan periode
        $period = ClosingPeriod::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('status', 'open')
            ->orderBy('period', 'desc')
            ->first();
        
        if (!$period) {
            $this->fail("No open closing period found in database");
        }

        $testDate = $period->start_date;

        $this->glService->syncByPeriod($period->uuid);
        $initialCount = GeneralLedger::where('account_uuid', $this->cashAccount->uuid)->count();

        // 2. Buat Jurnal Baru
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REF-NEW-001',
            'journal_date' => $testDate,
            'description' => 'Jurnal Baru',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 200, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 200],
            ]
        ]));

        // 3. Sync
        $this->glService->syncByPeriod($period->uuid);

        // 4. Verifikasi
        $this->assertEquals($initialCount + 1, GeneralLedger::where('account_uuid', $this->cashAccount->uuid)->count());
    }

    public function test_opening_balance_fiscal_year_reset()
    {
        // SEED PERIODS for testing (Ensure idempotency)
        ClosingPeriod::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->whereIn('period', ['2025-12', '2026-01'])
            ->forceDelete();

        ClosingPeriod::insert([
            ['uuid' => (string) Str::uuid(), 'branch_uuid' => $this->branch->uuid, 'period' => '2025-12', 'name' => 'Desember 2025', 'start_date' => strtotime('2025-12-01'), 'end_date' => strtotime('2025-12-31 23:59:59'), 'status' => 'open', 'deleted_at' => 0, 'created_at' => time(), 'updated_at' => time()],
            ['uuid' => (string) Str::uuid(), 'branch_uuid' => $this->branch->uuid, 'period' => '2026-01', 'name' => 'Januari 2026', 'start_date' => strtotime('2026-01-01'), 'end_date' => strtotime('2026-01-31 23:59:59'), 'status' => 'open', 'deleted_at' => 0, 'created_at' => time(), 'updated_at' => time()],
        ]);

        // 1. Create a Revenue entry in previous year
        $lastYear = '2025-12-31';
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REV-2025',
            'journal_date' => $lastYear,
            'description' => 'Pendapatan Tahun Lalu',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 1000],
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 1000, 'credit' => 0],
            ]
        ]));

        // 2. Check Opening Balance on Jan 1st Next Year
        $nextYear = '2026-01-01';
        
        // Revenue should be 0 (Reset)
        $revOpening = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->revenueAccount->uuid,
            'branch_uuid'  => $this->branch->uuid,
            'date_from'    => $nextYear,
            'date_to'      => $nextYear,
        ]));
        $this->assertEquals(0, $revOpening['opening_balance'], "Revenue should reset at year-end");

        // Cash (Asset) should be 1000 (Carryforward + Initial from seeder)
        $cashOpening = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->cashAccount->uuid,
            'branch_uuid'  => $this->branch->uuid,
            'date_from'    => $nextYear,
            'date_to'      => $nextYear,
        ]));
        // Initial cash from seeder is 100,000,000
        $this->assertEquals(100000000 + 1000, $cashOpening['opening_balance'], "Asset should carry forward");
    }

    /**
     * Test Skenario: Filter Audit (Keyword, Nominal Range, Reconciliation)
     */
    public function test_ledger_filters()
    {
        $period = ClosingPeriod::query()->where('branch_uuid', $this->branch->uuid)->orderBy('period', 'desc')->first();
        $testDate = $period->start_date;

        // 1. Create multiple journals with different amounts and descriptions
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FLT-001',
            'journal_date' => $testDate,
            'description' => 'Pembelian Alat Tulis',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 250000],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 250000, 'credit' => 0], // Just for test balance
            ]
        ]));

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FLT-002',
            'journal_date' => $testDate,
            'description' => 'Service AC Kantor',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 750000],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 750000, 'credit' => 0],
            ]
        ]));

        $this->glService->syncByPeriod($period->uuid);

        // 2. Test Keyword Filter
        $reportKeyword = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->cashAccount->uuid,
            'branch_uuid'  => $this->branch->uuid,
            'date_from'    => $testDate,
            'date_to'      => $testDate,
            'keyword'      => 'Tulis',
        ]));
        $this->assertCount(1, $reportKeyword['transactions']);
        $this->assertEquals('Pembelian Alat Tulis', $reportKeyword['transactions'][0]['description']);

        // 3. Test Nominal Range Filter
        $reportRange = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->cashAccount->uuid,
            'branch_uuid'  => $this->branch->uuid,
            'date_from'    => $testDate,
            'date_to'      => $testDate,
            'min_amount'   => 500000,
            'max_amount'   => 1000000,
        ]));
        $this->assertCount(1, $reportRange['transactions']);
        $this->assertEquals('Service AC Kantor', $reportRange['transactions'][0]['description']);

        // 4. Test Reconciliation Filter (Default false)
        $reportRec = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid'  => $this->cashAccount->uuid,
            'branch_uuid'   => $this->branch->uuid,
            'date_from'     => $testDate,
            'date_to'       => $testDate,
            'is_reconciled' => true,
        ]));
        $this->assertCount(0, $reportRec['transactions']);
    }

    public function test_ledger_trial_balance_consistency()
    {
        // 1. Dapatkan periode
        $period = ClosingPeriod::query()
            ->where('branch_uuid', $this->branch->uuid)
            ->where('status', 'open')
            ->first();

        // 2. Sync
        $this->glService->syncByPeriod($period->uuid);

        // 3. Check Balance
        $result = $this->glService->checkLedgerBalance($period->uuid);

        // 4. Verifikasi: Total Debit harus sama dengan Total Credit di level Ledger
        $this->assertTrue($result['is_balanced'], "Ledger is unbalanced! Debit: {$result['total_debit']}, Credit: {$result['total_credit']}");
        $this->assertEquals(0, $result['difference'], "Difference should be zero.");
    }
}

