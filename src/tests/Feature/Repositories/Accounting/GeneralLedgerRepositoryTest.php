<?php

namespace Tests\Feature\Repositories\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class GeneralLedgerRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private GeneralLedgerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GeneralLedgerRepository();
    }

    public function test_get_opening_balance_includes_opening_period_and_prior_mutations()
    {
        // Use factories which create required relationships (branch, company, etc.)
        $journal = Journal::factory()->create([
            'journal_date' => Carbon::parse('2026-01-01')->startOfDay()->timestamp,
            'status'       => 'posted',
            'reference_no' => 'J-001',
            'deleted_at'   => 0,
        ]);

        $account = Account::factory()->create([
            'type' => 'asset',
            'deleted_at' => 0,
            'branch_uuid' => $journal->branch_uuid,
            'company_uuid' => $journal->company_uuid,
        ]);
        $branchUuid = $journal->branch_uuid;

        // 1. Setup Opening Balance (the 'Opening' period record)
        DB::table('account_balances')->insert([
            'uuid'            => (string) Str::uuid(),
            'account_uuid'    => $account->uuid,
            'branch_uuid'     => $branchUuid,
            'company_uuid'    => $journal->company_uuid,
            'period'          => 'Opening',
            'opening_balance' => 1000,
            'total_debit'     => 0,
            'total_credit'    => 0,
            'closing_balance' => 1000,
            'deleted_at'      => 0,
            'created_at'      => time(),
            'updated_at'      => time(),
        ]);

        // 2. Setup Prior Mutation (journal line before target date)
        JournalLine::create([
            'journal_uuid'  => $journal->uuid,
            'account_uuid'  => $account->uuid,
            'branch_uuid'   => $branchUuid,
            'debit'         => 500,
            'credit'        => 0,
        ]);

        $balance = $this->repository->getOpeningBalance($account->uuid, '2026-02-01', $branchUuid);

        // Expected: 1000 (opening) + 500 (debit mutation) = 1500
        $this->assertEquals(1500, $balance);
    }

    public function test_get_journal_lines_returns_transactions_in_range()
    {
        $journal = Journal::factory()->create([
            'journal_date' => Carbon::parse('2026-02-15')->startOfDay()->timestamp,
            'status'       => 'posted',
            'reference_no' => 'J-FEB',
            'deleted_at'   => 0,
        ]);

        $account = Account::factory()->create(['deleted_at' => 0]);
        $branchUuid = $journal->branch_uuid;

        JournalLine::create([
            'journal_uuid' => $journal->uuid,
            'account_uuid' => $account->uuid,
            'branch_uuid'  => $branchUuid,
            'debit'        => 200,
            'credit'       => 0,
        ]);

        $lines = $this->repository->getJournalLines($account->uuid, '2026-02-01', '2026-02-28', $branchUuid);

        $this->assertCount(1, $lines);
        $this->assertEquals('J-FEB', $lines[0]->reference_no);
    }
}
