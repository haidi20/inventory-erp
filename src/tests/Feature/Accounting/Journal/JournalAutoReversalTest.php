<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Jobs\ProcessAutoReversalJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalAutoReversalTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $expenseAccount;
    protected Account $payableAccount;
    protected Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->journalService = app(JournalService::class);

        // Setup Branch from Seeder
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        // Setup Accounts from Seeder
        $this->expenseAccount = Account::where('type', 'expense')->where('is_header', false)->firstOrFail();
        $this->payableAccount = Account::where('type', 'liability')->where('is_header', false)->firstOrFail();
        $this->cashAccount = Account::where('type', 'asset')->where('is_header', false)->where('code', 'like', '1%')->firstOrFail();

        // Setup Accounting Periods — reset periods agar tidak overlap dengan seeder (now-based)
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::where('branch_uuid', $this->branch->uuid)->delete();
            ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => '2026-02', 'name' => 'Periode 2026-02', 'status' => 'open', 'start_date' => '2026-02-01', 'end_date' => '2026-02-28']);
            ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Periode 2026-03', 'status' => 'open', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31']);
        });
    }

    protected function createPeriod(string $periodString)
    {
        return ClosingPeriod::updateOrCreate(
            ['branch_uuid' => $this->branch->uuid, 'period' => $periodString],
            [
                'name' => "Periode " . $periodString,
                'status' => 'open',
                'start_date' => Carbon::parse($periodString . '-01')->startOfMonth()->toDateString(),
                'end_date' => Carbon::parse($periodString . '-01')->endOfMonth()->toDateString(),
            ]
        );
    }

    /**
     * @test
     * Skenario 1.1: Reversing Otomatis untuk Akrual Beban
     */
    public function test_auto_reversal_for_expense_accrual()
    {
        // Step 1: Create Journal with Reversing Flag
        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'ACCRUAL-101',
            'journal_date' => '2026-02-28',
            'description' => 'Akrual Gaji Januari 2024',
            'reference_type' => JournalReferenceType::GENERAL, // default for manual
            'lines' => [
                ['account_uuid' => $this->expenseAccount->uuid, 'debit' => 10000000, 'credit' => 0],
                ['account_uuid' => $this->payableAccount->uuid, 'debit' => 0, 'credit' => 10000000],
            ],
            'reversal' => [
                'is_auto_reverse' => true,
                'reversal_date' => '2026-03-01',
            ]
        ];

        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));

        $this->assertNotNull($journal);
        $this->assertEquals('pending_reversal', $journal->reversal_status);
        $this->assertEquals(
            '2026-03-01',
            Carbon::createFromTimestamp((int) $journal->reversal_date, config('app.timezone'))->toDateString()
        );

        // Step 2: Run Job scheduler
        Carbon::setTestNow('2026-03-01 00:00:00');

        $job = new ProcessAutoReversalJob();
        $job->handle($this->journalService);

        // Step 3: Verify reversal journal created
        $reversalJournal = Journal::where('reference_type', JournalReferenceType::REVERSAL)
            ->where('reference_uuid', $journal->uuid)
            ->first();

        $this->assertNotNull($reversalJournal);
        $this->assertEquals(
            '2026-03-01',
            Carbon::createFromTimestamp((int) $reversalJournal->journal_date, config('app.timezone'))->toDateString()
        );
        $this->assertStringContainsString('REVERSAL:', $reversalJournal->description);

        // Verify flipped lines
        $reversalLines = $reversalJournal->lines;
        $this->assertCount(2, $reversalLines);

        $expenseLine = $reversalLines->firstWhere('account_uuid', $this->expenseAccount->uuid);
        $payableLine = $reversalLines->firstWhere('account_uuid', $this->payableAccount->uuid);

        $this->assertEquals(10000000, (float)$expenseLine->credit);
        $this->assertEquals(0, (float)$expenseLine->debit);

        $this->assertEquals(10000000, (float)$payableLine->debit);
        $this->assertEquals(0, (float)$payableLine->credit);

        // Verify original journal status
        $refreshedJournal = $journal->refresh();
        $this->assertEquals('reversed', $refreshedJournal->reversal_status);
        $this->assertEquals($reversalJournal->uuid, $refreshedJournal->reversal_journal_uuid);
    }

    /**
     * @test
     * Skenario 1.2: Reversing Manual
     */
    public function test_manual_reversal()
    {
        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'ACCRUAL-002',
            'journal_date' => '2026-02-01',
            'description' => 'Bayar Prepaid',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->expenseAccount->uuid, 'debit' => 1200000, 'credit' => 0],
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 1200000],
            ]
        ];

        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));

        $reversal = $this->journalService->reverseJournal($journal->uuid, [
            'reversal_date' => '2026-03-01',
            'description_prefix' => 'MANUAL REV:'
        ]);

        $this->assertNotNull($reversal);
        $refreshedJournal = $journal->refresh();
        $this->assertEquals('reversed', $refreshedJournal->reversal_status);
        $this->assertEquals($reversal->uuid, $refreshedJournal->reversal_journal_uuid);
    }

    /**
     * @test
     * Skenario 1.3: Validasi Tidak Bisa Reverse Periode Tertutup
     */
    public function test_cannot_reverse_in_closed_period()
    {
        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'ACCRUAL-103',
            'journal_date' => '2026-02-28',
            'description' => 'Akrual',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->expenseAccount->uuid, 'debit' => 50000, 'credit' => 0],
                ['account_uuid' => $this->payableAccount->uuid, 'debit' => 0, 'credit' => 50000],
            ]
        ];

        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));

        // Close February period
        $febPeriod = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('period', '2026-03')->first();
        $febPeriod->status = 'closed';
        $febPeriod->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reverse journal in closed period');

        $this->journalService->reverseJournal($journal->uuid, [
            'reversal_date' => '2026-03-01'
        ]);
    }
}
