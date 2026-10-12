<?php

namespace Tests\Unit\Services\Accounting;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Ledger\Repositories\AuditLogRepository;
use Illuminate\Support\Facades\Auth;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Repositories\JournalRepositoryInterface;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Repositories\RecurringJournalRepository;
use Tests\TestCase;
use App\Modules\Finance\Journal\Services\TransactionCodeService;
use App\Modules\Core\Identity\Models\User;
use Mockery;

class JournalServiceTest extends TestCase
{
    private $repositoryMock;
    private $accountSettingMock;
    private $branchMock;
    private $transactionCodeMock;
    private JournalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(JournalRepositoryInterface::class);
        $this->accountSettingMock = Mockery::mock(AccountSettingRepository::class);
        $this->branchMock = Mockery::mock(BranchService::class);
        $this->transactionCodeMock = Mockery::mock(TransactionCodeService::class);
        
        $this->service = new JournalService(
            $this->repositoryMock,
            $this->accountSettingMock,
            $this->branchMock,
            $this->transactionCodeMock,
            Mockery::mock(AuditLogRepository::class),
            Mockery::mock(RecurringJournalRepository::class)
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_journal_validates_unbalanced_entries()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => 'branch-1',
            'reference_uuid' => 'ref-1',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => 'acc-1', 'debit' => 100, 'credit' => 0],
                ['account_uuid' => 'acc-2', 'debit' => 0, 'credit' => 90] // Unbalanced
            ]
        ]);

        $this->accountSettingMock->shouldReceive('hasHeaderAccounts')->andReturn(false);
        $this->accountSettingMock->shouldReceive('hasInactiveAccounts')->andReturn(false);
        $this->accountSettingMock->shouldReceive('findPeriodByDate')->andReturn(new ClosingPeriod([
            'uuid' => 'period-1',
            'status' => 'open',
        ]));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('unbalanced');
        
        $this->service->createJournal($dto);
    }

    public function test_create_journal_prevents_posting_to_header_accounts()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => 'branch-1',
            'reference_uuid' => 'ref-1',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => 'acc-1', 'debit' => 100, 'credit' => 0],
                ['account_uuid' => 'acc-2', 'debit' => 0, 'credit' => 100]
            ]
        ]);

        $this->accountSettingMock->shouldReceive('hasHeaderAccounts')->andReturn(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('header account');
        
        $this->service->createJournal($dto);
    }

    public function test_approve_journal_checks_segregation_of_duties()
    {
        $journal = JournalEntity::fromArray([
            'branch_uuid' => 'branch-1',
            'reference_no' => 'JR-001',
            'reference_uuid' => 'ref-1',
            'journal_date' => time(),
            'reference_type' => JournalReferenceType::GENERAL,
            'created_by' => 'user-1',
            'status' => JournalStatus::DRAFT,
            'workflow_status' => JournalWorkflowStatus::DRAFT,
            'is_system' => false,
            'lines' => [],
        ]);

        // Mock current user as the same as creator
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('uuid')->andReturn('user-1');
        Auth::shouldReceive('user')->andReturn($user);

        $this->repositoryMock->shouldReceive('findByIdForUpdate')->andReturn($journal);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Segregation of Duties');

        $this->service->approveJournal('j-123');
    }

    public function test_void_journal_calls_reverse_journal()
    {
        $uuid = 'j-123';
        $original = JournalEntity::fromArray([
            'branch_uuid' => 'b-1',
            'reference_no' => 'JR-001',
            'reference_uuid' => 'ref-1',
            'journal_date' => time(),
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'description' => 'Original journal',
            'lines' => [
                ['account_uuid' => 'acc-1', 'debit' => 100, 'credit' => 0],
                ['account_uuid' => 'acc-2', 'debit' => 0, 'credit' => 100],
            ],
        ]);
        $reversal = JournalEntity::fromArray([
            'branch_uuid' => 'b-1',
            'reference_no' => 'JR-001-REV',
            'reference_uuid' => 'ref-1',
            'journal_date' => time(),
            'reference_type' => JournalReferenceType::REVERSAL,
            'status' => JournalStatus::POSTED,
            'lines' => [],
        ]);
        
        $this->repositoryMock->shouldReceive('findById')->with($uuid)->andReturn($original);
        $this->accountSettingMock->shouldReceive('findPeriodByDate')->once()->andReturn(new ClosingPeriod([
            'uuid' => 'period-1',
            'status' => 'open',
        ]));
        $this->transactionCodeMock->shouldReceive('generate')
            ->with('b-1', JournalReferenceType::REVERSAL)
            ->andReturn('REV-001');
        
        $this->repositoryMock->shouldReceive('reverseJournal')
            ->once()
            ->with($uuid, Mockery::type(JournalEntity::class), ['status' => 'voided'])
            ->andReturn($reversal);

        $this->service->voidJournal($uuid);
        $this->assertTrue(true);
    }
}
