<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
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
use Exception;

class JournalWorkflowSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;
    protected Account $accKas;
    protected Account $accPendapatan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Test Branch', 'short_name' => 'TST', 'is_active' => true]);
        $this->accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit']);
        $this->accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit']);

        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 28: Simulasi Flow Approval (Draft -> Pending Manager -> Approved/Posted)
     */
    public function test_full_approval_workflow_simulation(): void
    {
        // 1. Create Draft
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'WF-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Workflow Simulation',
            'status' => JournalStatus::DRAFT,
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->accPendapatan->uuid, 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $journal = $this->service->createJournal($dto);
        $this->assertEquals(JournalStatus::DRAFT, $journal->status);
        $this->assertEquals(JournalWorkflowStatus::DRAFT, $journal->workflow_status);

        $journal = $this->service->submitJournalForApproval($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::PENDING_SUPERVISOR, $journal->workflow_status);

        // 2. Approve stage 1 (Supervisor) -> Pending Manager
        $journal = $this->service->approveJournal($journal->uuid);
        $this->assertEquals(JournalStatus::DRAFT, $journal->status);
        $this->assertEquals(JournalWorkflowStatus::PENDING_MANAGER, $journal->workflow_status);

        // 3. Approve stage 2 (Manager) -> Approved & Posted
        $journal = $this->service->approveJournal($journal->uuid);
        
        // 4. Verification
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $journal->workflow_status);
        
        // 5. Try Re-Approve (Should fail)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Jurnal sudah diposting.");
        $this->service->approveJournal($journal->uuid);
    }
}
