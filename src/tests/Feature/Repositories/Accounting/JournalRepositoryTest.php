<?php

namespace Tests\Feature\Repositories\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Entities\JournalLineEntity;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Repositories\JournalRepository;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Modules\Finance\Journal\Models\TransactionSequence;

class JournalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private JournalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new JournalRepository();
    }

    public function test_create_with_lines_saves_header_and_details()
    {
        $branch = Branch::factory()->create();
        $branchUuid = $branch->uuid;
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'transaction_code' => 'J-123',
            'reference_no' => 'REF-123',
            'journal_date' => time(),
            'status' => 'draft',
            'description' => 'Test Journal',
            'lines' => [
                [
                    'account_uuid' => $account1->uuid,
                    'debit' => 100,
                    'credit' => 0
                ],
                [
                    'account_uuid' => $account2->uuid,
                    'debit' => 0,
                    'credit' => 100
                ]
            ]
        ]);

        $journal = $this->repository->createWithLines($dto, 'actor-uuid');

        $this->assertDatabaseHas('journals', ['uuid' => $journal->uuid, 'transaction_code' => 'J-123']);
        $this->assertCount(2, $journal->lines);
    }

    public function test_get_next_sequence_increments_last_index()
    {
        $branch = Branch::factory()->create();
        $branchUuid = $branch->uuid;
        $companyUuid = $branch->company_uuid;
        $prefix = 'BKM';
        $period = '202604';

        $seq1 = $this->repository->getNextSequence($branchUuid, $companyUuid, $prefix, $period, 'user-1');
        $this->assertEquals(1, $seq1);

        $seq2 = $this->repository->getNextSequence($branchUuid, $companyUuid, $prefix, $period, 'user-1');
        $this->assertEquals(2, $seq2);
        
        $this->assertDatabaseHas('transaction_sequences', [
            'branch_uuid' => $branchUuid,
            'last_index' => 2
        ]);
    }

    public function test_upsert_journal_updates_existing_by_ref_no()
    {
        $branch = Branch::factory()->create();
        $original = Journal::create([
            'uuid' => (string) Str::uuid(),
            'transaction_code' => 'TX-DUPE',
            'reference_no' => 'REF-DUPE',
            'branch_uuid' => $branch->uuid,
            'journal_date' => time(),
            'status' => 'draft'
        ]);

        $dto = JournalEntity::fromArray([
            'transaction_code' => 'TX-DUPE',
            'reference_no' => 'REF-DUPE',
            'description' => 'Updated description',
            'status' => 'posted',
            'lines' => []
        ]);

        $this->repository->upsertJournal($dto);

        $this->assertDatabaseHas('journals', [
            'uuid' => $original->uuid,
            'description' => 'Updated description',
            'status' => 'posted'
        ]);
    }
}
