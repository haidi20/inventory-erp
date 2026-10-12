<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Support\Facades\Auth;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Exceptions\UnauthorizedException;
use App\Modules\Core\Identity\Models\User;

class JournalConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branchA;
    protected Branch $branchB;
    protected Account $accKas;
    protected Account $accRevenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        
        $this->branchA = Branch::create(['name' => 'Branch A', 'short_name' => 'BRA', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Branch B', 'short_name' => 'BRB', 'is_active' => true]);
        
        $this->accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit']);
        $this->accRevenue = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit']);

        ClosingPeriod::create(['branch_uuid' => $this->branchA->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-A', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        ClosingPeriod::create(['branch_uuid' => $this->branchB->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-B', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 27.1: Branch Isolation Check (Gagal jika post ke cabang lain)
     */
    public function test_branch_isolation_prevents_unauthorized_post(): void
    {
        // 1. Setup User Branch A
        $user = User::factory()->create(['branch_uuid' => $this->branchA->uuid]);
        Auth::login($user);

        // 2. Prepare DTO untuk Branch B (Harusnya Gagal)
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branchB->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-FAIL-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Cross Branch Attempt',
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 100],
            ],
        ]);

        // 3. Assertion
        $this->expectException(UnauthorizedException::class);
        $this->service->createJournal($dto);
    }

    /**
     * Skenario 27.2: Consolidated Query (Berhasil melihat semua cabang)
     */
    public function test_consolidated_query_returns_all_branches(): void
    {
        Auth::logout(); // Logout to ignore isolation in createJournal for setup
        
        // 1. Post di Branch A
        $this->service->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branchA->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-A',
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Journal A',
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 100],
            ],
        ]));

        // 2. Post di Branch B
        $this->service->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branchB->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-B',
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Journal B',
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 200, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 200],
            ],
        ]));

        // 3. Query tanpa filter branch_uuid
        $results = $this->service->getJournalReport([]);

        // 4. Assertion
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('reference_no', 'REF-A'));
        $this->assertTrue($results->contains('reference_no', 'REF-B'));
    }

    /**
     * Skenario 27.3: Row-Level Security Bypass Attempt
     * User Cabang A mencoba melihat detail jurnal Cabang B.
     */
    public function test_prevents_unauthorized_detail_access(): void
    {
        Auth::logout();
        
        // 1. Setup Jurnal di Branch B
        $journalB = $this->service->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branchB->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-SEC-B',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accKas->uuid, 'debit' => 500, 'credit' => 0],
                ['account_uuid' => $this->accRevenue->uuid, 'debit' => 0, 'credit' => 500],
            ],
        ]));

        // 2. Login as User Branch A
        $userA = User::factory()->create(['branch_uuid' => $this->branchA->uuid]);
        $this->actingAs($userA);

        // 3. Assertion: Mencoba akses detail Jurnal B
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Akses ditolak');
        $this->service->getJournalDetail($journalB->uuid);
    }
}
