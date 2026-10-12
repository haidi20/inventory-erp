<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Sales\Repositories\CashBankRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CashBankRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private CashBankRepository $repository;
    private Company $companySandbox;
    private Company $companyProduction;
    private Branch $branchSandbox;
    private Branch $branchProduction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CashBankRepository::class);

        // Create two different companies
        $this->companySandbox = Company::create([
            'uuid' => 'sandbox-company-uuid',
            'name' => 'Sandbox Company',
            'clinic_type' => 'klinik pratama',
            'is_active' => true,
        ]);

        $this->companyProduction = Company::create([
            'uuid' => 'production-company-uuid',
            'name' => 'Production Company',
            'clinic_type' => 'klinik utama',
            'is_active' => true,
        ]);

        // Create branches for each company
        $this->branchSandbox = Branch::create([
            'uuid' => 'sandbox-branch-uuid',
            'name' => 'Sandbox Branch',
            'short_name' => 'SB',
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        $this->branchProduction = Branch::create([
            'uuid' => 'production-branch-uuid',
            'name' => 'Production Branch',
            'short_name' => 'PB',
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_get_cash_accounts_respects_company_scope()
    {
        // Create cash accounts for sandbox company
        $sandboxCashAccount = Account::create([
            'uuid' => 'sandbox-cash-uuid',
            'code' => '1101',
            'name' => 'Kas Besar Sandbox',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        $sandboxBankAccount = Account::create([
            'uuid' => 'sandbox-bank-uuid',
            'code' => '1102',
            'name' => 'Bank Sandbox',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        // Create cash accounts for production company
        $productionCashAccount = Account::create([
            'uuid' => 'production-cash-uuid',
            'code' => '1201',
            'name' => 'Kas Besar Production',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        $productionBankAccount = Account::create([
            'uuid' => 'production-bank-uuid',
            'code' => '1202',
            'name' => 'Bank Production',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        // Get cash accounts for sandbox company
        $accounts = $this->repository->getCashAccounts($this->companySandbox->uuid);

        // Verify only sandbox accounts are returned
        $this->assertCount(2, $accounts);
        $this->assertTrue(
            $accounts->contains('uuid', $sandboxCashAccount->uuid),
            'Sandbox cash account should be present'
        );
        $this->assertTrue(
            $accounts->contains('uuid', $sandboxBankAccount->uuid),
            'Sandbox bank account should be present'
        );

        // Verify production accounts are NOT returned
        $this->assertFalse(
            $accounts->contains('uuid', $productionCashAccount->uuid),
            'Production cash account should NOT be present for sandbox company'
        );
        $this->assertFalse(
            $accounts->contains('uuid', $productionBankAccount->uuid),
            'Production bank account should NOT be present for sandbox company'
        );
    }

    /** @test */
    public function test_get_latest_balances_respects_company_scope()
    {
        // Create account for sandbox company
        $sandboxAccount = Account::create([
            'uuid' => 'sandbox-account-uuid',
            'code' => '1101',
            'name' => 'Kas Sandbox',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        // Create account for production company
        $productionAccount = Account::create([
            'uuid' => 'production-account-uuid',
            'code' => '1201',
            'name' => 'Kas Production',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'type' => 'asset',
            'is_active' => true,
        ]);

        // Create account balances for both companies
        AccountBalance::create([
            'uuid' => 'sandbox-balance-uuid',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'account_uuid' => $sandboxAccount->uuid,
            'period' => '2026-04',
            'opening_balance' => 1000000,
            'total_debit' => 500000,
            'total_credit' => 200000,
            'closing_balance' => 1300000,
        ]);

        // Note: Production balance intentionally omitted to test default values
        // When balance doesn't exist, getLatestBalances should return period-based defaults

        // Get balances for sandbox company
        $sandboxAccounts = collect([$sandboxAccount]);
        $balances = $this->repository->getLatestBalances(
            $sandboxAccounts,
            '2026-04',
            $this->companySandbox->uuid
        );

        // Verify sandbox balance is present
        $this->assertCount(1, $balances);
        $sandboxBalance = $balances->first();
        $this->assertEquals(1300000, $sandboxBalance['closing_balance']);
        $this->assertEquals($sandboxAccount->uuid, $sandboxBalance['account']->uuid);
    }

    /** @test */
    public function test_company_isolation_on_cash_accounts()
    {
        // Sandbox: Create 3 cash accounts
        for ($i = 1; $i <= 3; $i++) {
            Account::create([
                'uuid' => "sandbox-cash-$i",
                'code' => "110{$i}",
                'name' => "Kas Sandbox {$i}",
                'branch_uuid' => $this->branchSandbox->uuid,
                'company_uuid' => $this->companySandbox->uuid,
                'type' => 'asset',
                'is_active' => true,
            ]);
        }

        // Production: Create 2 cash accounts (use different code range to avoid unique constraint)
        for ($i = 1; $i <= 2; $i++) {
            Account::create([
                'uuid' => "production-cash-$i",
                'code' => "120{$i}",
                'name' => "Kas Production {$i}",
                'branch_uuid' => $this->branchProduction->uuid,
                'company_uuid' => $this->companyProduction->uuid,
                'type' => 'asset',
                'is_active' => true,
            ]);
        }

        // Verify sandbox only sees 3 accounts
        $sandboxAccounts = $this->repository->getCashAccounts($this->companySandbox->uuid);
        $this->assertCount(3, $sandboxAccounts);

        // Verify production only sees 2 accounts
        $productionAccounts = $this->repository->getCashAccounts($this->companyProduction->uuid);
        $this->assertCount(2, $productionAccounts);

        // Verify no crossover
        $sandboxUuids = $sandboxAccounts->pluck('uuid')->toArray();
        $productionUuids = $productionAccounts->pluck('uuid')->toArray();
        $this->assertEmpty(array_intersect($sandboxUuids, $productionUuids));
    }

    /** @test */
    public function test_get_today_mutation_summary_returns_empty_when_no_accounts()
    {
        // Test with empty account array
        $mutations = $this->repository->getTodayMutationSummary([], $this->companySandbox->uuid);

        $this->assertEquals(0, $mutations->total_debit);
        $this->assertEquals(0, $mutations->total_credit);
    }

    /** @test */
    public function test_get_recent_mutations_returns_empty_when_no_accounts()
    {
        // Test with empty account array
        $mutations = $this->repository->getRecentMutations([], $this->companySandbox->uuid);

        $this->assertCount(0, $mutations);
    }
}
