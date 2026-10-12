<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class JournalDisasterRecoverySimulationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;
    protected Account $accKas;
    protected Account $accRevenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Recovery Branch', 'short_name' => 'REC', 'is_active' => true]);
        $this->accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit']);
        $this->accRevenue = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit']);

        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-Active', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 30.1: Simulasi Void Jurnal (Batal)
     */
    public function test_void_journal_creates_reversal_and_updates_status(): void
    {
        // 1. Create a posted journal
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-ORIG-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Original Journal',
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 5000, 'credit' => 0],
                ['account_uuid' => $this->accKas->uuid, 'debit' => 0, 'credit' => 5000],
            ],
        ]);
        $original = $this->service->createJournal($dto);

        // 2. Void it
        $reversal = $this->service->voidJournal($original->uuid);

        // 3. Assertions
        $refreshedOriginal = $original->refresh();
        $this->assertEquals(JournalStatus::VOIDED, $refreshedOriginal->status, "Status jurnal asli harus voided");
        $this->assertEquals('reversed', $refreshedOriginal->reversal_status, "Reversal status jurnal asli harus reversed");
        $this->assertEquals($reversal->uuid, $refreshedOriginal->reversal_journal_uuid, "Reversal ID harus terhubung");

        // Assert reversal lines (should be swapped)
        $reversalLines = collect($reversal->lines);
        $this->assertEquals(5000, $reversalLines->firstWhere('debit', 0)->credit);
        $this->assertEquals(5000, $reversalLines->firstWhere('credit', 0)->debit);
    }

    /**
     * Skenario 30.2: Proteksi Re-Reversal
     */
    public function test_cannot_reverse_already_reversed_journal(): void
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-DOUBLE-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 100],
            ],
        ]);
        $journal = $this->service->createJournal($dto);

        $this->service->voidJournal($journal->uuid);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Journal is already reversed.");
        $this->service->voidJournal($journal->uuid);
    }

    /**
     * Skenario 30.3: Proteksi Periode Tertutup (Closed Period)
     */
    public function test_cannot_void_in_closed_period(): void
    {
        // 1. Create journal while period is OPEN
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-CLOSED-OK' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 100],
            ],
        ]);
        $journal = $this->service->createJournal($dto);

        // 2. Close the period
        $period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->first();
        $period->update(['status' => 'closed']);

        // 3. Try void (Should Fail)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot reverse journal in closed period");
        $this->service->voidJournal($journal->uuid);
    }
}
