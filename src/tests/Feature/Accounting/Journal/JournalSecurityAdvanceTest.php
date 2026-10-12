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
use Illuminate\Support\Facades\Auth;

class JournalSecurityAdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;
    protected User $approver;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->approver = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($this->user);
        
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit']);
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 3.1.3: Segregation of Duties (SOD)
     * Inputer dilarang Approve jurnal sendiri.
     */
    public function test_inputer_cannot_approve_own_journal()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'SOD-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->journalService->submitJournalForApproval($journal->uuid);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Segregation of Duties');
        $this->journalService->approveJournal($journal->uuid);
    }

    /**
     * Skenario 3.1.3: Different User CAN Approve
     */
    public function test_different_user_can_approve_journal()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'SOD-OK-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->journalService->submitJournalForApproval($journal->uuid);

        // Switch user to Approver
        $this->actingAs($this->approver);
        
        $approved = $this->journalService->approveJournal($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::PENDING_MANAGER, $approved->workflow_status);
        
        // Approve again to final
        $final = $this->journalService->approveJournal($journal->uuid);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $final->workflow_status);
        $this->assertEquals(JournalStatus::POSTED, $final->status);
    }
}
