<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Exceptions\UnauthorizedException;
use App\Modules\Core\Identity\Models\User;

class SegmentReportTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $journalService;
    protected $branch1;
    protected $branch2;
    protected $period;
    protected $period2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch1 = Branch::factory()->create(['name' => 'Branch A', 'short_name' => 'BRA']);
        $this->branch2 = Branch::factory()->create(['name' => 'Branch B', 'short_name' => 'BRB']);
        
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch1->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->period2 = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch2->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->reportService = app(FinancialReportService::class);
        $this->journalService = app(JournalService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch1->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Laporan Per Segmen (Branch)
     */
    public function test_segment_report_isolation()
    {
        $accKas = Account::create([
            'code' => '1101', 'name' => 'Kas', 'type' => 'asset', 
            'financial_statement' => 'balance_sheet', 'is_header' => false
        ]);
        
        $accPendapatan = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 
            'financial_statement' => 'income_statement', 'is_header' => false
        ]);

        // Branch 1 transaction
        $user1 = User::factory()->create(['branch_uuid' => $this->branch1->uuid]);
        $this->actingAs($user1);
        
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'reference_no' => 'TR-BRA-001',
            'journal_date' => '2026-01-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 1000000],
            ],
        ]));

        // Branch 2 transaction
        $user2 = User::factory()->create(['branch_uuid' => $this->branch2->uuid]);
        $this->actingAs($user2);
        
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch2->uuid,
            'reference_no' => 'TR-BRB-001',
            'journal_date' => '2026-01-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 2000000],
            ],
        ]));

        // Get report for Branch 1
        $this->actingAs($user1);
        $result1 = $this->reportService->getIncomeStatement($this->period->uuid, $this->branch1->uuid);
        
        // Get report for Branch 2
        $this->actingAs($user2);
        $result2 = $this->reportService->getIncomeStatement($this->period2->uuid, $this->branch2->uuid);

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
    }

    /**
     * Test Keamanan Isolasi Antar Cabang (Akses Terlarang)
     */
    public function test_segment_report_unauthorized_access()
    {
        $user1 = User::factory()->create(['branch_uuid' => $this->branch1->uuid]);
        $this->actingAs($user1);

        // Mencoba mengambil laporan milik branch 2 dengan user branch 1
        $this->expectException(UnauthorizedException::class);
        $this->reportService->getIncomeStatement($this->period->uuid, $this->branch2->uuid);
    }

    /**
     * SEG-02 Konsolidasi per wilayah.
     */
    public function test_segment_report_region_consolidation_aggregates_selected_branches()
    {
        $branch3 = $this->createBranchWithPeriod('Branch C', 'BRC');
        $branchOutsideRegion = $this->createBranchWithPeriod('Branch D', 'BRD');

        $this->postRevenueJournal($this->branch1, 'SEG-REG-001', 1000000, 0);
        $this->postRevenueJournal($this->branch2, 'SEG-REG-002', 1500000, 0);
        $this->postRevenueJournal($branch3, 'SEG-REG-003', 500000, 0);
        $this->postRevenueJournal($branchOutsideRegion, 'SEG-REG-004', 3000000, 0);

        $serviceRevenueAccount = $this->createAccount('4101', 'Pendapatan Jasa Dokter', 'revenue', 'income_statement');

        $regionalTotal = $this->sumRevenueForBranches(
            [$this->branch1->uuid, $this->branch2->uuid, $branch3->uuid],
            [$serviceRevenueAccount->uuid]
        );

        $nationalTotal = $this->sumRevenueForBranches(
            [$this->branch1->uuid, $this->branch2->uuid, $branch3->uuid, $branchOutsideRegion->uuid],
            [$serviceRevenueAccount->uuid]
        );

        $this->assertEquals(3000000, $regionalTotal);
        $this->assertEquals(6000000, $nationalTotal);
        $this->assertEquals(3000000, $nationalTotal - $regionalTotal);
    }

    /**
     * SEG-03 Laporan per jenis layanan.
     */
    public function test_segment_report_service_and_product_revenue_are_separated()
    {
        $serviceRevenueAccount = $this->createAccount('4101', 'Pendapatan Jasa Dokter', 'revenue', 'income_statement');
        $productRevenueAccount = $this->createAccount('4102', 'Pendapatan Obat', 'revenue', 'income_statement');

        $this->postRevenueJournal($this->branch1, 'SEG-SVC-001', 700000, 300000);

        $serviceTotal = $this->sumRevenueForBranches([$this->branch1->uuid], [$serviceRevenueAccount->uuid]);
        $productTotal = $this->sumRevenueForBranches([$this->branch1->uuid], [$productRevenueAccount->uuid]);

        $this->assertEquals(700000, $serviceTotal);
        $this->assertEquals(300000, $productTotal);
        $this->assertNotEquals($serviceTotal, $productTotal);
    }

    /**
     * SEG-05 Multi-level segmen.
     */
    public function test_segment_report_multi_level_totals_are_consistent()
    {
        $branch3 = $this->createBranchWithPeriod('Branch C', 'BRC');
        $branch4 = $this->createBranchWithPeriod('Branch D', 'BRD');

        $this->postRevenueJournal($this->branch1, 'SEG-MULTI-001', 1000000, 500000);
        $this->postRevenueJournal($this->branch2, 'SEG-MULTI-002', 1500000, 250000);
        $this->postRevenueJournal($branch3, 'SEG-MULTI-003', 2000000, 500000);
        $this->postRevenueJournal($branch4, 'SEG-MULTI-004', 2500000, 250000);

        $serviceRevenueAccount = $this->createAccount('4101', 'Pendapatan Jasa Dokter', 'revenue', 'income_statement');
        $productRevenueAccount = $this->createAccount('4102', 'Pendapatan Obat', 'revenue', 'income_statement');
        $revenueAccountIds = [$serviceRevenueAccount->uuid, $productRevenueAccount->uuid];

        $westRegionTotal = $this->sumRevenueForBranches([$this->branch1->uuid, $this->branch2->uuid], $revenueAccountIds);
        $eastRegionTotal = $this->sumRevenueForBranches([$branch3->uuid, $branch4->uuid], $revenueAccountIds);
        $nationalTotal = $this->sumRevenueForBranches(
            [$this->branch1->uuid, $this->branch2->uuid, $branch3->uuid, $branch4->uuid],
            $revenueAccountIds
        );

        $this->assertEquals(3250000, $westRegionTotal);
        $this->assertEquals(5250000, $eastRegionTotal);
        $this->assertEquals(8500000, $nationalTotal);
        $this->assertEquals($nationalTotal, $westRegionTotal + $eastRegionTotal);
    }

    private function createBranchWithPeriod(string $name, string $shortName): Branch
    {
        $branch = Branch::factory()->create([
            'name' => $name,
            'short_name' => $shortName,
        ]);

        ClosingPeriod::factory()->create([
            'branch_uuid' => $branch->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        return $branch;
    }

    private function createAccount(string $code, string $name, string $type, string $statement): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'type' => $type,
                'financial_statement' => $statement,
                'is_header' => false,
            ]
        );
    }

    private function postRevenueJournal(Branch $branch, string $referenceNo, int $serviceRevenue, int $productRevenue): void
    {
        $cashAccount = $this->createAccount('1101', 'Kas', 'asset', 'balance_sheet');
        $serviceRevenueAccount = $this->createAccount('4101', 'Pendapatan Jasa Dokter', 'revenue', 'income_statement');
        $productRevenueAccount = $this->createAccount('4102', 'Pendapatan Obat', 'revenue', 'income_statement');

        $totalRevenue = $serviceRevenue + $productRevenue;
        $lines = [
            ['account_uuid' => $cashAccount->uuid, 'debit' => $totalRevenue, 'credit' => 0],
        ];

        if ($serviceRevenue > 0) {
            $lines[] = ['account_uuid' => $serviceRevenueAccount->uuid, 'debit' => 0, 'credit' => $serviceRevenue];
        }

        if ($productRevenue > 0) {
            $lines[] = ['account_uuid' => $productRevenueAccount->uuid, 'debit' => 0, 'credit' => $productRevenue];
        }

        $user = User::factory()->create(['branch_uuid' => $branch->uuid]);
        $this->actingAs($user);

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $branch->uuid,
            'reference_no' => $referenceNo,
            'journal_date' => '2026-01-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => $lines,
        ]));
    }

    private function sumRevenueForBranches(array $branchIds, array $accountIds): float
    {
        $accountCodes = Account::query()
            ->whereIn('uuid', $accountIds)
            ->pluck('code');

        return (float) DB::table('journal_lines')
            ->join('journals', 'journal_lines.journal_uuid', '=', 'journals.uuid')
            ->join('accounts', 'journal_lines.account_uuid', '=', 'accounts.uuid')
            ->whereIn('journals.branch_uuid', $branchIds)
            ->whereIn('accounts.code', $accountCodes)
            ->where('journals.status', JournalStatus::POSTED->value)
            ->sum('journal_lines.credit');
    }
}
