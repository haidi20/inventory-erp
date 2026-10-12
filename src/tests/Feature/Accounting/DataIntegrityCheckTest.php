<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataIntegrityCheckTest extends TestCase
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
            'name' => 'Integrity Branch',
            'short_name' => 'INT',
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

    public function test_valid_journal_keeps_header_and_lines_consistent(): void
    {
        $journal = $this->journalService->createJournal($this->makeBalancedJournal('INT-001', 500000));

        $this->assertDatabaseHas('journals', [
            'uuid' => $journal->uuid,
            'reference_no' => 'INT-001',
        ]);
        $lines = collect($journal->lines);
        $this->assertSame(2, $lines->count());
        $this->assertEquals(500000.0, (float) $lines->sum('debit'));
        $this->assertEquals(500000.0, (float) $lines->sum('credit'));

        $orphanCount = DB::table('journal_lines')
            ->leftJoin('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
            ->whereNull('journals.uuid')
            ->count();

        $this->assertSame(0, $orphanCount);
    }

    public function test_failed_unbalanced_journal_leaves_no_partial_records(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('unbalanced');

        try {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid,
                'journal_date' => now()->toDateString(),
                'reference_no' => 'INT-ROLLBACK-001',
                'reference_type' => JournalReferenceType::GENERAL,
                'description' => 'Should rollback',
                'lines' => [
                    ['account_uuid' => $this->cashAccount->uuid, 'debit' => 500000, 'credit' => 0],
                    ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 250000],
                ],
            ]));
        } finally {
            $this->assertSame(0, Journal::where('reference_no', 'INT-ROLLBACK-001')->count());
            $this->assertSame(0, JournalLine::count());
        }
    }

    public function test_duplicate_reference_number_does_not_create_second_journal(): void
    {
        $this->journalService->createJournal($this->makeBalancedJournal('INT-DUP-001', 300000));

        try {
            $this->journalService->createJournal($this->makeBalancedJournal('INT-DUP-001', 300000));
            $this->fail('Duplicate reference number should be rejected.');
        } catch (Exception $e) {
            $this->assertStringContainsString('sudah digunakan', $e->getMessage());
        }

        $this->assertSame(1, Journal::where('reference_no', 'INT-DUP-001')->count());
        $this->assertSame(2, JournalLine::count());
    }

    private function makeBalancedJournal(string $referenceNo, float $amount): JournalEntity
    {
        return JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => $referenceNo,
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Integrity journal',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]);
    }
}
