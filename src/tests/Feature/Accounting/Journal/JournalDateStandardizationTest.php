<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use Database\Factories\Master\BranchFactory;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use Database\Factories\ClosingPeriodFactory;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalDateStandardizationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $account1;
    protected Account $account2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        
        $this->branch = BranchFactory::new()->create();
        $this->account1 = Account::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->account2 = Account::factory()->create(['branch_uuid' => $this->branch->uuid]);

        // Setup periode
        ClosingPeriodFactory::new()->create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-04',
            'start_date' => strtotime('2026-04-01'),
            'end_date' => strtotime('2026-04-30'),
            'status' => 'open'
        ]);
    }

    /** @test */
    public function it_can_create_journal_using_unix_timestamp()
    {
        $timestamp = strtotime('2026-04-15 10:30:00');
        
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JRN-TEST-001',
            'journal_date' => $timestamp,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->account1->uuid, 'debit' => 1000, 'credit' => 0, 'branch_uuid' => $this->branch->uuid],
                ['account_uuid' => $this->account2->uuid, 'debit' => 0, 'credit' => 1000, 'branch_uuid' => $this->branch->uuid],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);

        $this->assertEquals($timestamp, $journal->journal_date);
        $this->assertDatabaseHas('journals', [
            'uuid' => $journal->uuid,
            'journal_date' => $timestamp
        ]);
    }

    /** @test */
    public function it_validates_period_correctly_using_integer_timestamp()
    {
        // Periode 2026-04 adalah 'open'
        $validTimestamp = strtotime('2026-04-10');
        
        $dtoValid = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JRN-VALID',
            'journal_date' => $validTimestamp,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->account1->uuid, 'debit' => 100, 'credit' => 0, 'branch_uuid' => $this->branch->uuid],
                ['account_uuid' => $this->account2->uuid, 'debit' => 0, 'credit' => 100, 'branch_uuid' => $this->branch->uuid],
            ]
        ]);

        $journal = $this->journalService->createJournal($dtoValid);
        $this->assertNotNull($journal);

        // Tanggal di luar periode yang tersedia
        $invalidTimestamp = strtotime('2026-05-01');
        $dtoInvalid = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JRN-INVALID',
            'journal_date' => $invalidTimestamp,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->account1->uuid, 'debit' => 100, 'credit' => 0, 'branch_uuid' => $this->branch->uuid],
                ['account_uuid' => $this->account2->uuid, 'debit' => 0, 'credit' => 100, 'branch_uuid' => $this->branch->uuid],
            ]
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No accounting period defined for the date '2026-05-01'");
        
        $this->journalService->createJournal($dtoInvalid);
    }
}
