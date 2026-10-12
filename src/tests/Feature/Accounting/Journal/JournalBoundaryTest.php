<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Exception;
use Illuminate\Support\Carbon;

class JournalBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->branch_uuid = $this->branch->uuid;
        $this->user->save();
        
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit']);
        
    }

    /**
     * Skenario 3.6.1: Maximum Value Boundary
     */
    public function test_handles_large_trillion_scale_transaction()
    {
        $largeAmount = 999000000000.00; // 999 Miliar (Typical enterprise limit)
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'MAX-VAL-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => $largeAmount, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => $largeAmount],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertNotNull($journal);
        $this->assertEquals($largeAmount, (float) $journal->lines[0]->debit);
    }

    /**
     * Skenario 3.6.2: Minimum Value / Precision Boundary
     */
    public function test_handles_small_decimal_precision()
    {
        $smallAmount = 0.01; // Standard double precision
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'MIN-VAL-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => $smallAmount, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => $smallAmount],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertNotNull($journal);
        $this->assertEquals($smallAmount, (float) $journal->lines[0]->debit);
    }

    /**
     * Skenario 3.6.3: Date Boundary (Start of Month)
     */
    public function test_handles_exact_start_of_month_boundary()
    {
        $date = now()->startOfMonth()->toDateString();
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => $date,
            'reference_no' => 'DATE-BND-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertNotNull($journal);
        $this->assertEquals(
            $date,
            Carbon::createFromTimestamp((int) $journal->journal_date, config('app.timezone'))->toDateString()
        );
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
    }

    /**
     * Skenario 3.6.4: Sequence Rollover (Month-end reset)
     */
    public function test_transaction_sequence_resets_on_new_month()
    {
        // 1. Create for March
        $dateMarch = '2026-03-15';
        \Illuminate\Support\Carbon::setTestNow($dateMarch);
        
        ClosingPeriod::updateOrCreate(['branch_uuid' => $this->branch->uuid, 'period' => '2026-03'], ['name' => 'P-MAR', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open']);
        
        $dto1 = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => $dateMarch,
            'reference_no' => 'SEQ-MAR-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100]]
        ]);
        $j1 = $this->journalService->createJournal($dto1);
        $this->assertStringContainsString('2603', $j1->transaction_code); // Align with service generator (YYMM)

        // 2. Create for April
        $dateApril = '2026-04-01';
        Carbon::setTestNow($dateApril); // Simulate April 1st
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => '2026-04', 'name' => 'P-APR', 'start_date' => '2026-04-01', 'end_date' => '2026-04-30', 'status' => 'open']);
        
        $dto2 = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => $dateApril,
            'reference_no' => 'SEQ-APR-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100]]
        ]);
        $j2 = $this->journalService->createJournal($dto2);
        
        $this->assertStringContainsString('2604', $j2->transaction_code); // Matches April (YYMM)
        $this->assertStringContainsString('0001', $j2->transaction_code); // Should reset to 0001
        
        Carbon::setTestNow(); // Reset simulation
    }
}
