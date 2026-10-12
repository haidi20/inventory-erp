<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTransactionLockingTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->branch = Branch::create([
            'name' => 'Lock Branch',
            'short_name' => 'LCK',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Current Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);

        $this->cashAccount = Account::create([
            'code' => '1101',
            'name' => 'Kas',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);

        $this->revenueAccount = Account::create([
            'code' => '4101',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'financial_statement' => 'income_statement',
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    public function test_prevent_duplicate_reference_number(): void
    {
        $dto = $this->makeDto('LOCK-REF-001');

        $this->journalService->createJournal($dto);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No referensi 'LOCK-REF-001' sudah digunakan.");

        $this->journalService->createJournal($dto);
    }

    public function test_optimistic_locking_via_version(): void
    {
        $referenceNo = 'LOCK-VER-001';

        $created = $this->journalService->createJournal(
            $this->makeDto($referenceNo, 'Draft awal', JournalStatus::DRAFT)
        );

        $this->assertSame(1, (int) $created->version);

        $updatedV2 = $this->journalService->upsertJournal(
            $this->makeDto($referenceNo, 'Update versi 2', JournalStatus::DRAFT, 1)
        );
        $this->assertSame(2, (int) $updatedV2->version);

        $updatedV3 = $this->journalService->upsertJournal(
            $this->makeDto($referenceNo, 'Update versi 3', JournalStatus::DRAFT, 2)
        );
        $this->assertSame(3, (int) $updatedV3->version);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Concurrency Lock Error');

        $this->journalService->upsertJournal(
            $this->makeDto($referenceNo, 'Update stale', JournalStatus::DRAFT, 2)
        );
    }

    private function makeDto(
        string $referenceNo,
        string $description = 'Journal locking test',
        JournalStatus $status = JournalStatus::POSTED,
        int $version = 1
    ): JournalEntity {
        return JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => $referenceNo,
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => $description,
            'status' => $status,
            'version' => $version,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 250000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 250000],
            ],
        ]);
    }
}
