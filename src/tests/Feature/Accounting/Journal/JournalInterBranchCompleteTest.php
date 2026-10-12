<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Support\Facades\Auth;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Entities\JournalLineEntity;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithSandboxRuntime;
use App\Modules\Core\Identity\Models\User;

class JournalInterBranchCompleteTest extends TestCase
{
    use DatabaseMigrations, InteractsWithSandboxRuntime;

    protected JournalService $journalService;
    protected FinancialReportService $reportService;
    protected Company $demoCompany;
    protected Branch $branchHO;
    protected Branch $branchA;
    protected User $admin;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->reportService = app(FinancialReportService::class);

        // Setup User & Branches
        $this->demoCompany = $this->demoCompany();
        $this->admin = $this->actingAsDemoSuperadmin();
        
        $this->branchHO = $this->demoBranch();
        $this->branchA = Branch::create([
            'name' => 'Branch A',
            'short_name' => 'BRA',
            'company_uuid' => $this->demoCompany->uuid,
            'is_active' => true,
        ]);

        // Setup Periods
        $period = now()->format('Y-m');
        ClosingPeriod::create(['company_uuid' => $this->demoCompany->uuid, 'branch_uuid' => $this->branchHO->uuid, 'period' => $period, 'name' => 'HO Period', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        ClosingPeriod::create(['company_uuid' => $this->demoCompany->uuid, 'branch_uuid' => $this->branchA->uuid, 'period' => $period, 'name' => 'A Period', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);

        // Setup Accounts
        $scope = ['company_uuid' => $this->demoCompany->uuid, 'branch_uuid' => $this->branchHO->uuid];
        $this->accounts['cash'] = Account::create($scope + ['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['inventory'] = Account::create($scope + ['code' => '1401', 'name' => 'Inventory', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['clearing'] = Account::create($scope + ['code' => '1901', 'name' => 'Inter-branch Clearing', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['expense'] = Account::create($scope + ['code' => '6101', 'name' => 'Operational Expense', 'type' => 'expense', 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);
        $this->accounts['revenue'] = Account::create($scope + ['code' => '4101', 'name' => 'Service Revenue', 'type' => 'revenue', 'type_saldo' => 'credit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);

        // Setup Account Mappings
        AccountMapping::create($scope + ['key' => 'stok_adjustment.default', 'account_uuid' => $this->accounts['inventory']->uuid]);
        AccountMapping::create($scope + ['key' => 'biaya.default', 'account_uuid' => $this->accounts['expense']->uuid]);
    }

    /**
     * Skenario 46 & 50: Stock Transfer & Elimination
     */
    public function test_stock_transfer_and_elimination()
    {
        // Transfer 1.000.000 from HO to Branch A
        $amount = 1000000;

        $lines = [
            // Side HO (Credit Inventory, Debit Clearing)
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchHO->uuid, 'account_uuid' => $this->accounts['inventory']->uuid, 'debit' => 0, 'credit' => $amount]),
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchHO->uuid, 'account_uuid' => $this->accounts['clearing']->uuid, 'debit' => $amount, 'credit' => 0]),
            
            // Side Branch A (Debit Inventory, Credit Clearing)
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchA->uuid, 'account_uuid' => $this->accounts['inventory']->uuid, 'debit' => $amount, 'credit' => 0]),
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchA->uuid, 'account_uuid' => $this->accounts['clearing']->uuid, 'debit' => 0, 'credit' => $amount]),
        ];

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branchHO->uuid, // Header branch
            'reference_no' => 'IBT-ST-001',
            'journal_date' => now()->toDateString(),
            'reference_type' => JournalReferenceType::STOCK_ADJUSTMENT,
            'is_system' => true,
            'lines' => $lines
        ]);

        $journal = $this->journalService->createJournal($dto);
        $this->assertEquals(JournalStatus::POSTED, $journal->fresh()->status); // Interbranch should be posted automatically
        // Verification: Clearing account must net to zero across branches
        $clearingBalance = JournalLine::where('account_uuid', $this->accounts['clearing']->uuid)->sum(\DB::raw('debit - credit'));
        $this->assertEquals(0, (float)$clearingBalance, "Elimination failed: Clearing account balance should be zero.");
    }

    /**
     * Skenario 48 & 49: Cost Allocation & Consolidated Income Statement
     */
    public function test_cost_allocation_and_consolidation()
    {
        $amount = 500000;

        // 1. Post Revenue in Branch A (5.000.000)
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branchA->uuid,
            'reference_no' => 'REV-A-001',
            'journal_date' => now()->toDateString(),
            'reference_type' => JournalReferenceType::GENERAL,
            'is_system' => true,
            'status' => JournalStatus::POSTED,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 5000000],
            ]
        ]));

        // 2. Allocate HO Expense to Branch A
        $lines = [
            // HO pays cash, debit clearing
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchHO->uuid, 'account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => $amount]),
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchHO->uuid, 'account_uuid' => $this->accounts['clearing']->uuid, 'debit' => $amount, 'credit' => 0]),
            
            // Branch A receives expense, credit clearing
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchA->uuid, 'account_uuid' => $this->accounts['expense']->uuid, 'debit' => $amount, 'credit' => 0]),
            JournalLineEntity::fromArray(['branch_uuid' => $this->branchA->uuid, 'account_uuid' => $this->accounts['clearing']->uuid, 'debit' => 0, 'credit' => $amount]),
        ];

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branchHO->uuid,
            'reference_no' => 'IBT-EXP-001',
            'journal_date' => now()->toDateString(),
            'reference_type' => JournalReferenceType::EXPENSE,
            'lines' => $lines
        ]));

        $hoPeriod = ClosingPeriod::where('branch_uuid', $this->branchHO->uuid)
            ->where('period', now()->format('Y-m'))
            ->firstOrFail();
        $branchPeriod = ClosingPeriod::where('branch_uuid', $this->branchA->uuid)
            ->where('period', now()->format('Y-m'))
            ->firstOrFail();

        app(GeneralLedgerService::class)->syncByPeriod($hoPeriod->uuid);
        app(GeneralLedgerService::class)->syncByPeriod($branchPeriod->uuid);

        // 3. Verify Consolidated Report
        $consolidatedEntity = FinancialReportEntity::fromArray(['period_uuid' => $hoPeriod->uuid, 'branch_uuid' => null]);
        $report = $this->reportService->getIncomeStatement($consolidatedEntity); // Consolidated
        $this->assertEquals(5000000, $report['revenue']['total']);
        $this->assertEquals(500000, $report['expense']['total']);
        $this->assertEquals(4500000, $report['net_income']);
    }
}
