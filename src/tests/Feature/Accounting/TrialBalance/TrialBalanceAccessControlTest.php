<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Core\Authorization\Models\Role;
use Tests\TestCase;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use App\Modules\Core\Identity\Models\User;

/**
 * Skenario 20: Validasi Hak Akses dan Segregasi Tugas
 */
class TrialBalanceAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected TrialBalanceService $trialBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->trialBalanceService = app(TrialBalanceService::class);
    }

    /**
     * 20.1: RBAC - user hanya bisa akses data cabang sendiri via HasBranchScope
     */
    public function test_rbac_branch_isolation()
    {
        // Buat 2 cabang
        $branchA = Branch::factory()->create(['name' => 'Cabang A', 'short_name' => 'CA', 'is_active' => true]);
        $branchB = Branch::factory()->create(['name' => 'Cabang B', 'short_name' => 'CB', 'is_active' => true]);

        $periodA = ClosingPeriod::create([
            'branch_uuid' => $branchA->uuid, 'period' => '2026-03', 'name' => 'Maret A',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);
        $periodB = ClosingPeriod::create([
            'branch_uuid' => $branchB->uuid, 'period' => '2026-03', 'name' => 'Maret B',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        // User A di Cabang A
        $userA = User::factory()->create(['branch_uuid' => $branchA->uuid]);

        // Buat akun dan jurnal di Cabang A
        $this->actingAs($userA);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accRev = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $branchA->uuid, 'reference_no' => 'RBAC-A-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 10000000, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 10000000],
            ],
        ]));

        // Buat jurnal di Cabang B (switch user)
        $userB = User::factory()->create(['branch_uuid' => $branchB->uuid]);
        $this->actingAs($userB);
        $accKasB = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accRevB = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $branchB->uuid, 'reference_no' => 'RBAC-B-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasB->uuid, 'debit' => 20000000, 'credit' => 0],
                ['account_uuid' => $accRevB->uuid, 'debit' => 0, 'credit' => 20000000],
            ],
        ]));

        // Sync kedua cabang
        app(GeneralLedgerService::class)->syncByPeriod($periodA->uuid);
        app(GeneralLedgerService::class)->syncByPeriod($periodB->uuid);

        // User A hanya melihat data Cabang A
        $this->actingAs($userA);
        $tbA = $this->trialBalanceService->generate($periodA->uuid, $branchA->uuid, true);

        // User B hanya melihat data Cabang B
        $this->actingAs($userB);
        $tbB = $this->trialBalanceService->generate($periodB->uuid, $branchB->uuid, true);

        $this->assertTrue($tbA->is_balanced);
        $this->assertTrue($tbB->is_balanced);
        $this->assertEquals(10000000, $tbA->totals->total_debit, 'Cabang A = 10M');
        $this->assertEquals(20000000, $tbB->totals->total_debit, 'Cabang B = 20M');
        $this->assertNotEquals($tbA->totals->total_debit, $tbB->totals->total_debit,
            'Data antar cabang harus terisolasi');
    }

    /**
     * 20.2: Data segregation - GL masing-masing cabang tidak tercampur
     */
    public function test_data_segregation_general_ledger()
    {
        $branchX = Branch::factory()->create(['name' => 'X', 'short_name' => 'BX', 'is_active' => true]);
        $branchY = Branch::factory()->create(['name' => 'Y', 'short_name' => 'BY', 'is_active' => true]);

        $periodX = ClosingPeriod::create([
            'branch_uuid' => $branchX->uuid, 'period' => '2026-03', 'name' => 'Maret X',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);
        $periodY = ClosingPeriod::create([
            'branch_uuid' => $branchY->uuid, 'period' => '2026-03', 'name' => 'Maret Y',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $userX = User::factory()->create(['branch_uuid' => $branchX->uuid]);
        $this->actingAs($userX);

        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accRev = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        // Post di X
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $branchX->uuid, 'reference_no' => 'SEG-X-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 5000000],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($periodX->uuid);

        // GL di branch Y harus kosong (tanpa BranchScope – query langsung)
        $glCountY = GeneralLedger::withoutGlobalScopes()
            ->where('branch_uuid', $branchY->uuid)->count();
        $glCountX = GeneralLedger::withoutGlobalScopes()
            ->where('branch_uuid', $branchX->uuid)->count();

        $this->assertEquals(0, $glCountY, 'GL cabang Y harus kosong');
        $this->assertGreaterThan(0, $glCountX, 'GL cabang X harus ada data');
    }

    /**
     * 20.3: Segregasi tugas - created_by tidak boleh sama dengan approver
     */
    public function test_segregation_of_duties_creator_vs_approver()
    {
        $branchSod = Branch::factory()->create(['name' => 'SOD', 'short_name' => 'SOD', 'is_active' => true]);

        $creator = User::factory()->create(['branch_uuid' => $branchSod->uuid]);
        $approver = User::factory()->create(['branch_uuid' => $branchSod->uuid]);

        $this->actingAs($creator);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);

        // Buat jurnal oleh creator
        $journal = Journal::create([
            'branch_uuid' => $branchSod->uuid, 'transaction_code' => 'SOD/001',
            'reference_no' => 'SOD-001', 'reference_uuid' => 0,
            'journal_date' => '2026-03-15', 'status' => 'draft',
            'workflow_status' => 'pending', 'created_by' => $creator->uuid
        ]);

        // Validasi SOD: creator != approver
        $isCreator = $journal->created_by == $creator->uuid;
        $isSameAsApprover = $journal->created_by == $approver->uuid;

        $this->assertTrue($isCreator, 'Jurnal harus dibuat oleh creator');
        $this->assertFalse($isSameAsApprover, 'Creator tidak boleh sama dengan approver');

        // Approve oleh approver
        $this->actingAs($approver);
        $journal->update([
            'workflow_status' => 'approved', 'status' => 'posted',
            'updated_by' => $approver->uuid
        ]);

        $this->assertNotEquals($journal->created_by, $journal->fresh()->updated_by,
            'Approver (updated_by) harus berbeda dari creator (created_by)');
    }

    /**
     * 20.4: Role-based akses - verifikasi role dan permission tersedia
     */
    public function test_role_permission_structure()
    {
        // Buat roles
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Administrator']);
        $accountantRole = Role::create(['name' => 'accountant', 'label' => 'Accountant']);
        $auditorRole = Role::create(['name' => 'auditor', 'label' => 'Auditor']);

        // Buat permissions
        $viewTB = Permission::create(['name' => 'view_trial_balance', 'label' => 'View Trial Balance']);
        $editJournal = Permission::create(['name' => 'edit_journal', 'label' => 'Edit Journal']);
        $exportReport = Permission::create(['name' => 'export_report', 'label' => 'Export Report']);

        // Assign permissions ke roles
        $adminRole->permissions()->attach([$viewTB->uuid, $editJournal->uuid, $exportReport->uuid]);
        $accountantRole->permissions()->attach([$viewTB->uuid, $editJournal->uuid]);
        $auditorRole->permissions()->attach([$viewTB->uuid, $exportReport->uuid]);

        // Verifikasi
        $this->assertEquals(3, $adminRole->permissions()->count(), 'Admin = 3 permissions');
        $this->assertEquals(2, $accountantRole->permissions()->count(), 'Accountant = 2 permissions');
        $this->assertEquals(2, $auditorRole->permissions()->count(), 'Auditor = 2 permissions');

        // Verifikasi auditor tidak punya edit_journal
        $auditorPerms = $auditorRole->permissions()->pluck('name')->toArray();
        $this->assertNotContains('edit_journal', $auditorPerms,
            'Auditor tidak boleh punya akses edit journal');
        $this->assertContains('view_trial_balance', $auditorPerms,
            'Auditor harus bisa view trial balance');
    }

    /**
     * 20.5: User role assignment dan multi-branch user
     */
    public function test_user_role_assignment()
    {
        $branch = Branch::factory()->create(['name' => 'Role Test', 'short_name' => 'RT', 'is_active' => true]);
        $user = User::factory()->create(['branch_uuid' => $branch->uuid]);

        $role = Role::create(['name' => 'finance_manager', 'label' => 'Finance Manager']);
        $perm1 = Permission::create(['name' => 'manage_trial_balance', 'label' => 'Manage TB']);
        $perm2 = Permission::create(['name' => 'manage_journals', 'label' => 'Manage Journals']);
        $role->permissions()->attach([$perm1->uuid, $perm2->uuid]);

        // Assign role ke user
        $user->roles()->attach($role->uuid);

        // Verifikasi
        $this->assertTrue($user->roles()->where('name', 'finance_manager')->exists(),
            'User harus memiliki role finance_manager');

        $rolePerms = $user->roles()->first()->permissions()->pluck('name')->toArray();
        $this->assertContains('manage_trial_balance', $rolePerms);
        $this->assertContains('manage_journals', $rolePerms);
    }
}
