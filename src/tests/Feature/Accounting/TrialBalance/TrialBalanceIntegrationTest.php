<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialBalanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected TrialBalanceService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::create(['name' => 'Integration Branch', 'short_name' => 'INT', 'is_active' => true]);
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        $this->journalService = app(JournalService::class);
        $this->service = app(TrialBalanceService::class);
    }

    public function test_can_generate_trial_balance_via_sp()
    {
        // 1. Setup Data Manually
        // branch already created in setUp()
        
        $kasAccount = Account::create([
            'code' => '1101', 'name' => 'Kas', 'type' => 'asset', 
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $pendapatanAccount = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);
        
        $period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'March 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $amount = 1000000;
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'      => $this->branch->uuid,
            'reference_no'   => 'TB-TEST-DYNAMIC',
            'reference_uuid'   => 999,
            'journal_date'   => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'description'    => 'Test Trial Balance',
            'lines'          => [
                ['account_uuid' => $kasAccount->uuid, 'debit' => $amount, 'credit' => 0],
                ['account_uuid' => $pendapatanAccount->uuid, 'debit' => 0, 'credit' => $amount],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($period->uuid);

        // 2. Execution
        $tb = $this->service->generate($period->uuid, $this->branch->uuid, true);

        // 3. Assertion
        $this->assertTrue($tb->is_balanced, "Trial Balance must be balanced");
        $this->assertEquals($amount, $tb->totals->total_debit);
        
        $accMatch = collect($tb->accounts)->firstWhere('account_code', '1101');
        $this->assertNotNull($accMatch, "Account 1101 must exist in TB results");
        $this->assertEquals($amount, $accMatch->total_debit);
    }

    public function test_validate_balance_logic()
    {
        // branch already created in setUp()
        $period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'March 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $validation = $this->service->validateBalance($period->uuid, $this->branch->uuid);
        $this->assertTrue($validation['is_balanced']);
    }
}
