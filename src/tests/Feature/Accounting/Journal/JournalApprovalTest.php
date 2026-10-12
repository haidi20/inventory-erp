<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
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

class JournalApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $revenueAccount;
    protected Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->journalService = app(JournalService::class);

        // Setup Branch from Seeder
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        // Setup Accounts from Seeder
        $this->revenueAccount = Account::where('type', 'revenue')->where('is_header', false)->firstOrFail();
        $this->cashAccount = Account::where('type', 'asset')->where('is_header', false)->where('code', 'like', '1%')->firstOrFail();

        // Setup Accounting Periods - Use withoutEvents to bypass overlap validation in test setup
        ClosingPeriod::withoutEvents(function() {
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-02'],
                [
                    'name' => "Periode 2026-02",
                    'status' => 'open',
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-02-28',
                ]
            );
        });
    }

    /**
     * @test
     * Skenario 6.1: Membuat Jurnal Draft tidak posting ke Ledger
     */
    public function test_create_journal_as_draft_skips_ledger()
    {
        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'DRAFT-001',
            'journal_date' => '2026-02-15',
            'description' => 'Jurnal Draft Pengujian',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'draft',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 500000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 500000],
            ]
        ];

        // Hitung saldo sebelum posting (misal 0 atau seeded amount)
        // Kita gunakan ledger helper atau query table langsung jika ada table general_ledgers.
        // Di aplikasi ini postToLedger mengupdate table general_ledgers? 
        // Let's assume there's a table general_ledgers or it just updates accounts.
        // Let's just assert that status is 'draft' first and no exception thrown.
        
        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));

        $this->assertNotNull($journal);
        $this->assertEquals(JournalStatus::DRAFT, $journal->status);
        
        $period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('period', '2026-02')->firstOrFail();
        app(GeneralLedgerService::class)->syncByPeriod($period->uuid);

        // Cek if general_ledger entry dibuat? 
        $hasLedger = GeneralLedger::where('journal_uuid', $journal->uuid)->exists();
        $this->assertFalse($hasLedger, "Baris buku besar tidak boleh dibuat untuk status draft");
    }

    /**
     * @test
     * Skenario 6.1: Menyetujui Jurnal Draft
     */
    public function test_approve_draft_journal()
    {
        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'DRAFT-002',
            'journal_date' => '2026-02-15',
            'description' => 'Jurnal Draft Untuk Approve',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'draft',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 300000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 300000],
            ]
        ];

        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));
        $this->assertEquals(JournalStatus::DRAFT, $journal->status);

        // Approve it!
        $approvedJournal = $this->journalService->approveJournal($journal->uuid);

        $this->assertEquals(JournalStatus::POSTED, $approvedJournal->status);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $approvedJournal->workflow_status);

        $period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('period', '2026-02')->firstOrFail();
        app(GeneralLedgerService::class)->syncByPeriod($period->uuid);

        // Berkas buku besar harus sudah terbuat
        $hasLedger = GeneralLedger::where('journal_uuid', $journal->uuid)->exists();
        $this->assertTrue($hasLedger, "Baris buku besar harus dibuat setelah disetujui");
    }
}
