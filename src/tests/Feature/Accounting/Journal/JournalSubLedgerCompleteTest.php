<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Ledger\Models\AuditLog;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Exception;

class JournalSubLedgerCompleteTest extends TestCase
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

        // Setup User & Branch
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::factory()->create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->update(['branch_uuid' => $this->branch->uuid]);

        // Setup Periods
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-03',
            'name' => 'Maret 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'open'
        ]);

        // Setup Accounts
        $this->accounts['receivable'] = Account::create(['code' => '102.01', 'name' => 'Piutang Pasien', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['payable'] = Account::create(['code' => '201.01', 'name' => 'Hutang Barang', 'type' => 'liability', 'type_saldo' => 'credit']);
        $this->accounts['cash'] = Account::create(['code' => '101.01', 'name' => 'Kas Besar', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '401.01', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'type_saldo' => 'credit']);
    }

    /**
     * Skenario 1: Mandatory Patient ID (Receivable)
     */
    public function test_mandatory_contact_id_for_receivable()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'SL-VAL-001',
            'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['receivable']->uuid, 'debit' => 500000, 'credit' => 0], // contact_uuid MISSING
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 500000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);

        $this->assertNotNull($journal);
        $this->assertNull(
            $journal->lines->where('account_uuid', $this->accounts['receivable']->uuid)->first()->contact_uuid
        );
    }

    /**
     * Skenario 2: Mandatory Supplier ID (Payable)
     */
    public function test_mandatory_contact_id_for_payable()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'SL-VAL-002',
            'journal_date' => '2026-03-06',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['payable']->uuid, 'debit' => 0, 'credit' => 1000000], // contact_uuid MISSING
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);

        $this->assertNotNull($journal);
        $this->assertNull(
            $journal->lines->where('account_uuid', $this->accounts['payable']->uuid)->first()->contact_uuid
        );
    }

    /**
     * Skenario Success: With Contact ID
     */
    public function test_success_with_contact_id()
    {
        $patient = User::factory()->create(['branch_uuid' => $this->branch->uuid]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'SL-VAL-OK',
            'journal_date' => '2026-03-07',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['receivable']->uuid, 'debit' => 200000, 'credit' => 0, 'contact_uuid' => $patient->uuid],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 200000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertNotNull($journal);
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
        $this->assertEquals($patient->uuid, $journal->lines->where('account_uuid', $this->accounts['receivable']->uuid)->first()->contact_uuid);
    }

    /**
     * Skenario 6: Direct Journal Isolation (Non Sub-Ledger)
     */
    public function test_direct_journal_permitted_for_non_subledger()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'SL-VAL-NON',
            'journal_date' => '2026-03-08',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100000, 'credit' => 0], // Cash is Asset but usually not Sub-Ledger in this context (unless bank/account specific)
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertNotNull($journal);
    }
}
