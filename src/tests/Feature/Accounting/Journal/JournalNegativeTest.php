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

class JournalNegativeTest extends TestCase
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
        
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit', 'is_active' => true, 'is_header' => false]);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit', 'is_active' => true, 'is_header' => false]);
        $this->accounts['header'] = Account::create(['code' => '1000', 'name' => 'Asset Header', 'type' => 'asset', 'type_saldo' => 'debit', 'is_active' => true, 'is_header' => true]);
        $this->accounts['inactive'] = Account::create(['code' => '9999', 'name' => 'Old Account', 'type' => 'expense', 'type_saldo' => 'debit', 'is_active' => false, 'is_header' => false]);

        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 1.3.1: Zero Value Rejection
     */
    public function test_rejects_zero_value_journal()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'NEG-ZERO-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 0],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Journal total amount must be greater than zero.");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 1.3.2: Single Line Rejection
     */
    public function test_rejects_single_line_journal()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'NEG-SINGLE-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Journal must have at least two lines (Debit and Credit).");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 1.3.3: Unbalanced Journal
     */
    public function test_rejects_unbalanced_journal()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'NEG-UNBAL-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 99],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Journal unbalanced");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 1.1.3: Header Account Posting Prevention
     */
    public function test_rejects_posting_to_header_account()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'NEG-HEADER-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['header']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot post to header account");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 1.1.3: Inactive Account Posting Prevention
     */
    public function test_rejects_posting_to_inactive_account()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'NEG-INACTIVE-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['inactive']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Account is inactive");
        
        $this->journalService->createJournal($dto);
    }
}
