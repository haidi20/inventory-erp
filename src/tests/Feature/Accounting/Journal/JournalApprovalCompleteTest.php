<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AuditLog;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalApprovalCompleteTest extends TestCase
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

        // Setup User & Branch
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::factory()->create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->update(['branch_uuid' => $this->branch->uuid]);

        // Setup Periods
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-03',
            'name' => 'Maret 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'open'
        ]);

        // Setup Accounts
        $this->accounts['cash'] = Account::create(['code' => '101', 'name' => 'Kas', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '401', 'name' => 'Pendapatan', 'type' => 'revenue', 'type_saldo' => 'credit']);
        $this->accounts['expense'] = Account::create(['code' => '501', 'name' => 'Biaya', 'type' => 'expense', 'type_saldo' => 'debit']);
    }

    /**
     * Skenario 1: Multi-Level Approval (Supervisor -> Manager -> Approved)
     */
    public function test_multi_level_approval_flow()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'ML-APP-001',
            'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['expense']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        // Create initial journal
        $journal = $this->journalService->createJournal($dto);
        $this->assertEquals(JournalWorkflowStatus::DRAFT, $journal->workflow_status); // Still draft/new

        // 1. Submit for Approval
        $journal = $this->journalService->submitJournalForApproval($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::PENDING_SUPERVISOR, $journal->workflow_status);

        // 2. Supervisor Approves -> Move to pending_manager
        $supervisor = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($supervisor);
        $journal = $this->journalService->approveJournal($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::PENDING_MANAGER, $journal->workflow_status);

        // 3. Manager Approves -> Move to approved & posted
        $manager = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($manager);
        $journal = $this->journalService->approveJournal($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $journal->workflow_status);
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
    }

    /**
     * Skenario 2: Rejection with Revision
     */
    public function test_rejection_and_resubmission()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REJ-REV-001',
            'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal = $this->journalService->submitJournalForApproval($journal->uuid);

        // Reject by supervisor
        $reason = "Data kurang detail";
        $journal = $this->journalService->rejectJournal($journal->uuid, $reason);

        $this->assertEquals(JournalWorkflowStatus::REJECTED, $journal->workflow_status);
        $this->assertStringContainsString($reason, $journal->description);

        // Resubmit
        $journal = $this->journalService->submitJournalForApproval($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::PENDING_SUPERVISOR, $journal->workflow_status);
    }

    /**
     * Skenario 3: Auto-Approval Threshold (< 1M)
     */
    public function test_auto_approval_threshold()
    {
        // Case A: Amount 500k (Under 1M) -> Should Auto-Approve
        $dtoUnder = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'AUTO-001',
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 500000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 500000],
            ]
        ]);

        $journalA = $this->journalService->createJournal($dtoUnder);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $journalA->workflow_status);
        $this->assertEquals(JournalStatus::POSTED, $journalA->status);

        // Case B: Amount 2M (Over 1M) -> Should STAY Draft/New
        $dtoOver = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'MANUAL-001',
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        $journalB = $this->journalService->createJournal($dtoOver);
        $this->assertNotEquals(JournalWorkflowStatus::APPROVED, $journalB->workflow_status);
    }

    /**
     * Skenario 6: Approval Payload & Audit Log
     */
    public function test_approval_audit_trail()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'AUDIT-APP-001',
            'journal_date' => '2026-03-20',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 3000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 3000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal = $this->journalService->submitJournalForApproval($journal->uuid);
        
        $supervisor = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($supervisor);
        $journal = $this->journalService->approveJournal($journal->uuid); // to pending_manager
        
        $manager = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($manager);
        $journal = $this->journalService->approveJournal($journal->uuid); // to approved

        $logs = AuditLog::where('entity_uuid', $journal->uuid)
                         ->where('entity_type', Journal::class)
                         ->orderBy('id', 'asc')
                         ->get();

        $this->assertTrue($logs->contains('event', 'created'));
        $this->assertTrue($logs->contains('event', 'submitted_for_approval'));
        $this->assertTrue($logs->contains('event', 'approved_at_level_pending_manager'));
        $this->assertTrue($logs->contains('event', 'approved_at_level_approved'));
    }
}
