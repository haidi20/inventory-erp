<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PointInTimeRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected User $admin;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->journalService = app(JournalService::class);
        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
        $this->branch = Branch::findOrFail($this->admin->branch_uuid);
        $this->cashAccount = Account::firstOrCreate(
            ['code' => '1199'],
            [
                'name' => 'Kas Recovery Test',
                'type' => 'asset',
                'type_saldo' => 'debit',
                'financial_statement' => 'balance_sheet',
                'is_header' => false,
                'is_active' => true,
            ]
        );
        $this->revenueAccount = Account::firstOrCreate(
            ['code' => '4199'],
            [
                'name' => 'Pendapatan Recovery Test',
                'type' => 'revenue',
                'type_saldo' => 'credit',
                'financial_statement' => 'income_statement',
                'is_header' => false,
                'is_active' => true,
            ]
        );

        $this->actingAs($this->admin);
    }

    public function test_point_in_time_balance_can_be_reconstructed_up_to_cutoff_date(): void
    {
        $dayOne = now()->startOfMonth()->toDateString();
        $dayTwo = now()->startOfMonth()->addDay()->toDateString();

        $this->postRevenueJournal('PITR-001', $dayOne, 100000);
        $this->postRevenueJournal('PITR-002', $dayTwo, 250000);

        $balanceAtDayOne = $this->balanceAsOf($this->cashAccount->uuid, $dayOne);
        $balanceAtDayTwo = $this->balanceAsOf($this->cashAccount->uuid, $dayTwo);

        $this->assertEquals(100000, $balanceAtDayOne);
        $this->assertEquals(350000, $balanceAtDayTwo);
    }

    private function postRevenueJournal(string $referenceNo, string $journalDate, int $amount): void
    {
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => $referenceNo,
            'journal_date' => $journalDate,
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]));
    }

    private function balanceAsOf(string $accountUuid, string $cutoffDate): float
    {
        $cutoffTimestamp = strtotime($cutoffDate . ' 23:59:59');

        return (float) DB::table('journal_lines')
            ->join('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
            ->where('journals.branch_uuid', $this->branch->uuid)
            ->where('journal_lines.account_uuid', $accountUuid)
            ->where('journals.journal_date', '<=', $cutoffTimestamp)
            ->where('journals.status', JournalStatus::POSTED->value)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));
    }
}
