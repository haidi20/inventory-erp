<?php

namespace Tests\Feature\Accounting\Journal;
use AppModelsRole;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Finance\Ledger\Models\AuditLog;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Exception;
use Illuminate\Support\Facades\Auth;

class JournalSecurityCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;
    protected Role $role;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);

        // Setup User & Branch
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->update(['branch_uuid' => $this->branch->uuid]);

        // Setup Role (Don't attach yet, test will attach or not)
        $this->role = Role::create(['name' => 'Tax Officer', 'is_active' => true]);

        // Setup Accounts
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit']);
    }

    /**
     * Skenario 54: Fiscal Correction Validation
     */
    public function test_fiscal_correction_tagging() // Renamed method
    {
        $this->user->roles()->attach($this->role->uuid); // Added role attachment
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open']);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => '2026-03-10',
            'reference_no' => 'FISCAL-001',
            'reference_type' => JournalReferenceType::ADJUSTMENT,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0, 'is_fiscal' => true], // Type MISSING
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tipe Fiskal (positive/negative) wajib");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 56: Backdated Posting Control (Blocked on CLOSED period)
     */
    public function test_cannot_post_to_closed_period()
    {
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-01',
            'name' => 'P-JAN',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'closed' // CLOSED!
        ]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => '2026-01-15',
            'reference_no' => 'BACKDATED-ERR',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot create/update journal entry. Period '2026-01' is closed.");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Skenario 55: Audit Trail Payload Integrity
     */
    public function test_audit_trail_payload_mapping()
    {
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open']);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => '2026-03-12',
            'reference_no' => 'AUDIT-TEST-01',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 1000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        
        $audit = AuditLog::where('entity_uuid', $journal->uuid)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($audit);
        $payload = json_decode(json_encode($audit->after), true);
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
        $this->assertEquals('AUDIT-TEST-01', $payload['reference_no']);
    }
}
