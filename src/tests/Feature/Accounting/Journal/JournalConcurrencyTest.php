<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Exception;
use Illuminate\Support\Facades\DB;

class JournalConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->branch_uuid = $this->branch->uuid;
        $this->user->save();
        
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit']);
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 1.2.1: Double Submission (Duplicate Reference)
     */
    public function test_prevents_duplicate_reference_submission()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-CONCUR-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        // First creation success
        $this->journalService->createJournal($dto);
        
        // Second creation with SAME DTO should fail
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No referensi 'REF-CONCUR-001' sudah digunakan");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 1.2.2: Parallel Approval (Double Click)
     */
    public function test_prevents_manual_parallel_approval()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-APP-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->journalService->submitJournalForApproval($journal->uuid);

        $approver = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($approver);

        // First approval success
        $this->journalService->approveJournal($journal->uuid);
        
        // Second approval attempt should fail because status is already Approved/Pending next level
        $manager = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($manager);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Jurnal sudah diposting.");
        
        $this->journalService->approveJournal($journal->uuid); // Stage 2 success
        $this->journalService->approveJournal($journal->uuid); // STAGE 3 FAIL (already posted)
    }

    /**
     * Skenario 3.1.1: High-Frequency Sequential Creation
     * Memastikan generate transaction_code tidak collision meski dipanggil beruntun.
     */
    public function test_high_frequency_sequential_journal_creation()
    {
        $referencePrefix = 'SEQ-STRESS-';
        $transactionCodes = [];

        for ($i = 1; $i <= 10; $i++) {
            $dto = JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid,
                'journal_date' => now()->toDateString(),
                'reference_no' => $referencePrefix . $i,
                'reference_type' => JournalReferenceType::GENERAL,
                'lines' => [
                    ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                    ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
                ]
            ]);

            $journal = $this->journalService->createJournal($dto);
            $this->assertEquals(JournalStatus::POSTED, $journal->status);
            $transactionCodes[] = $journal->transaction_code;
        }

        // Add a second journal creation and assertion for SOD fix
        $dto2 = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-SOD-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $j2 = $this->journalService->createJournal($dto2);
        
        $this->assertStringContainsString('/26', $j2->transaction_code); // Match YY format
        $this->assertStringContainsString(now()->format('m'), $j2->transaction_code); // Match current month

        // Verifikasi keunikan
        $this->assertCount(10, array_unique($transactionCodes));
        
        // Verifikasi urutan (Asumsi format suffix XXXX)
        $lastCode = end($transactionCodes);
        $this->assertStringContainsString('0010', $lastCode);
    }
}
