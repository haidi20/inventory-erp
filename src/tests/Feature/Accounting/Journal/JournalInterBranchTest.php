<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalInterBranchTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
    }

    public function test_journal_lines_can_belong_to_different_branches()
    {
        // 1. Create two separate branches
        $branchA = Branch::create(['name' => 'Cabang Jakarta', 'short_name' => 'JKT']);
        $branchB = Branch::create(['name' => 'Cabang Surabaya', 'short_name' => 'SUB']);

        // 2. Setup standard closing period for both branches so validation passes
        ClosingPeriod::create([
            'branch_uuid' => $branchA->uuid,
            'period'    => now()->format('Y-m'),
            'name'      => 'Periode JKT',
            'start_date'=> now()->startOfMonth()->toDateString(),
            'end_date'  => now()->endOfMonth()->toDateString(),
            'status'    => 'open',
        ]);

        ClosingPeriod::create([
            'branch_uuid' => $branchB->uuid,
            'period'    => now()->format('Y-m'),
            'name'      => 'Periode SUB',
            'start_date'=> now()->startOfMonth()->toDateString(),
            'end_date'  => now()->endOfMonth()->toDateString(),
            'status'    => 'open',
        ]);

        // 3. Create generic account
        $account = Account::create([
            'code' => '1000',
            'name' => 'Kas',
            'type' => 'asset',
            'type_saldo' => 'debit',
        ]);

        // Mock Account Mapping for validateMapping() step
        AccountMapping::create([
            'key'        => 'hutang.uji',
            'account_uuid' => $account->uuid,
            'type_saldo' => 'credit',
        ]);

        // 4. Build Journal Package with distinct lines-branch items
        $lines = [
            [
                'account_uuid' => $account->uuid,
                'debit'  => 50000,
                'credit' => 0,
                'branch_uuid' => $branchA->uuid, // Jakarta
            ],
            [
                'account_uuid' => $account->uuid,
                'debit'  => 0,
                'credit' => 50000,
                'branch_uuid' => $branchB->uuid, // Surabaya
            ],
        ];

        $dto = JournalEntity::fromArray([
            'branch_uuid'      => $branchA->uuid, // Header Cabang Jakarta
            'reference_no'   => 'TEST-BR-' . time(),
            'reference_uuid'   => 1,
            'reference_model'=> JournalReferenceModel::SUPPLIER,
            'journal_date'   => now()->toDateString(),
            'reference_type' => JournalReferenceType::DEBT_PAYMENT,
            'description'    => 'Transfer Antar Cabang (Inter-Branch)',
            'lines'          => $lines,
        ]);

        // 5. Action: Create Journal
        $journal = $this->journalService->createJournal($dto);

        // 6. Assertions
        $this->assertNotNull($journal->uuid);
        $this->assertCount(2, $journal->lines);

        // Line 1 should be Branch A (Jakarta)
        $line1 = $journal->lines->where('debit', 50000)->first();
        $this->assertEquals($branchA->uuid, $line1->branch_uuid);

        // Line 2 should be Branch B (Surabaya)
        $line2 = $journal->lines->where('credit', 50000)->first();
        $this->assertEquals($branchB->uuid, $line2->branch_uuid); // This fails in original setup!
    }
}
