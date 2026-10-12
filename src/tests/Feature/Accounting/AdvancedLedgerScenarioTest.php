<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedLedgerScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;
    protected $cashAccount;
    protected $revenueAccount;
    protected $glService;
    protected $journalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Klinik Audit Test', 'short_name' => 'KAT']);
        
        $this->cashAccount = Account::create([
            'code' => '1101.01',
            'name' => 'Kas Audit',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'level' => 3
        ]);

        $this->revenueAccount = Account::create([
            'code' => '4101.01',
            'name' => 'Pendapatan Audit',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'level' => 3
        ]);

        $this->glService = app(GeneralLedgerService::class);
        $this->journalService = app(JournalService::class);
    }

    protected function createPeriod($period, $start, $end, $status = 'open')
    {
        return ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => $period,
            'name' => $period,
            'start_date' => $start,
            'end_date' => $end,
            'status' => $status
        ]);
    }

    /**
     * Skenario 1: "Time-Travel" Saldo Awal (Opening Balance)
     */
    public function test_opening_balance_time_travel()
    {
        $this->createPeriod('2026-01', '2026-01-01', '2026-01-31');
        $this->createPeriod('2026-02', '2026-02-01', '2026-02-28');
        $this->createPeriod('2026-03', '2026-03-01', '2026-03-31');

        // 1. Jan 15th: Debit Rp 10.000.000
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JR-JAN-01',
            'journal_date' => '2026-01-15',
            'description' => 'Income Jan',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 10000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 10000000],
            ]
        ]));

        // 2. Feb 20th: Credit Rp 2.000.000 (Expense/Withdrawal)
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JR-FEB-01',
            'journal_date' => '2026-02-20',
            'description' => 'Expense Feb',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 2000000],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 2000000, 'credit' => 0],
            ]
        ]));

        // 3. Sync GL
        $this->glService->syncByPeriod(ClosingPeriod::where('period', '2026-01')->first()->uuid);
        $this->glService->syncByPeriod(ClosingPeriod::where('period', '2026-02')->first()->uuid);

        // 4. Tarik Laporan Maret (1 - 31 Maret)
        $report = $this->glService->getLedgerReport(new GeneralLedgerFilterEntity(
            account_uuid: $this->cashAccount->uuid,
            branch_uuid: $this->branch->uuid,
            date_from: '2026-03-01',
            date_to: '2026-03-31',
        ));

        // Assert: Saldo Awal Maret harus 10jt - 2jt = 8jt
        $this->assertEquals(8000000, $report['opening_balance'], "Saldo Awal Maret harus Rp 8.000.000");
        $this->assertCount(0, $report['transactions'], "Maret harusnya kosong transaksinya");
    }

    /**
     * Skenario 2: "The Storno Effect" (Audit Trail Immutability)
     */
    public function test_storno_immutability()
    {
        $this->createPeriod('2026-01', '2026-01-01', '2026-01-31');
        $this->createPeriod('2026-02', '2026-02-01', '2026-02-28');
        $this->createPeriod('2026-03', '2026-03-01', '2026-03-31');

        $testDate = '2026-03-10';
        Carbon::setTestNow($testDate);

        // 1. Insert Jurnal Salah: Debit Kas Rp 5.000.000
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JR-ERR-01',
            'journal_date' => $testDate,
            'description' => 'Wrong Entry',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 5000000],
            ]
        ]));

        // 2. Void Jurnal (Storno)
        $this->journalService->voidJournal($journal->uuid);

        // 3. Sync GL
        $periodId = ClosingPeriod::where('period', '2026-03')->first()->uuid;
        $this->glService->syncByPeriod($periodId);

        // 4. Verifikasi di GL
        $entries = GeneralLedger::where('account_uuid', $this->cashAccount->uuid)
            ->where('journal_date', strtotime($testDate))
            ->orderBy('reference_no', 'asc')
            ->get();

        // Assert: 
        // - Row 1: Original Debit 5jt
        // - Row 2: Storno Credit 5jt
        $this->assertCount(2, $entries, "Harus ada 2 baris (Orisinil + Storno)");
        $this->assertEquals(5000000, $entries[0]->debit);
        $this->assertEquals(5000000, $entries[1]->credit);
        $this->assertEquals(0, $entries[1]->balance, "Saldo akhir harus kembali ke 0");
        
        Carbon::setTestNow(); // Reset
    }

    /**
     * Skenario 3: "The Steel Door" (Period Locking Exception)
     */
    public function test_period_locking_steel_door()
    {
        $this->createPeriod('2026-01', '2026-01-01', '2026-01-31');

        // 1. Tutup Periode Januari
        $period = ClosingPeriod::where('period', '2026-01')->first();
        $period->update(['status' => 'closed']);

        // 2. Coba post ke Januari
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot create/update journal entry. Period '2026-01' is closed.");

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JR-BACKDATED',
            'journal_date' => '2026-01-25',
            'description' => 'Backdated attempt',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 1000],
            ]
        ]));
    }

    /**
     * Skenario 4: "Year-End Reset" (P&L vs Balance Sheet)
     */
    public function test_year_end_reset_logic()
    {
        // 1. Create 2025 Period (Must be first to satisfy consistency)
        $this->createPeriod('2025-12', '2025-12-01', '2025-12-31');
        $this->createPeriod('2026-01', '2026-01-01', '2026-01-31');

        // 2. Insert Revenue & Asset at Dec 31st 2025
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'YE-2025',
            'journal_date' => '2025-12-31',
            'description' => 'Year End 2025',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 5000000],
            ]
        ]));

        // 3. Periksa Jan 1st 2026
        $nextYear = '2026-01-01';
        
        // Asset (Cash) should carry forward
        $cashReport = $this->glService->getLedgerReport(new GeneralLedgerFilterEntity(
            account_uuid: $this->cashAccount->uuid,
            branch_uuid: $this->branch->uuid,
            date_from: $nextYear,
            date_to: $nextYear,
        ));
        $this->assertEquals(5000000, $cashReport['opening_balance'], "Asset harus carry forward ke tahun depan");

        // Revenue should reset to 0
        $revReport = $this->glService->getLedgerReport(new GeneralLedgerFilterEntity(
            account_uuid: $this->revenueAccount->uuid,
            branch_uuid: $this->branch->uuid,
            date_from: $nextYear,
            date_to: $nextYear,
        ));
        $this->assertEquals(0, $revReport['opening_balance'], "Revenue harus reset ke 0 di awal tahun fisik");
    }
}
