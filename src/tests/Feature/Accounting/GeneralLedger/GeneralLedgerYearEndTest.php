<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeneralLedgerYearEndTest extends TestCase
{
    use RefreshDatabase;

    protected $branch1;
    protected $branch2;
    protected $cashAccount;
    protected $revenueAccount;
    protected $glService;
    protected $journalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create(['name' => 'Branch Pusat', 'short_name' => 'B1']);
        $this->branch2 = Branch::create(['name' => 'Branch Cabang', 'short_name' => 'B2']);
        
        $this->cashAccount = Account::create([
            'code' => '1101.01',
            'name' => 'Kas Bank',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'level' => 3
        ]);

        $this->revenueAccount = Account::create([
            'code' => '4101.01',
            'name' => 'Pendapatan Jasa',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'level' => 3
        ]);

        $this->glService = app(GeneralLedgerService::class);
        $this->journalService = app(JournalService::class);

        // Create Periods across year boundary
        $this->createPeriod($this->branch1->uuid, '2025-12', '2025-12-01', '2025-12-31');
        $this->createPeriod($this->branch1->uuid, '2026-01', '2026-01-01', '2026-01-31');
        
        $this->createPeriod($this->branch2->uuid, '2026-01', '2026-01-01', '2026-01-31');
    }

    protected function createPeriod($branchId, $period, $start, $end, $status = 'open')
    {
        return ClosingPeriod::withoutEvents(function () use ($branchId, $period, $start, $end, $status) {
            return ClosingPeriod::create([
                'branch_uuid' => $branchId,
                'period'      => $period,
                'name'        => "Period $period",
                'start_date'  => $start,
                'end_date'    => $end,
                'status'      => $status,
            ]);
        });
    }

    public function test_pnl_reset_at_year_end()
    {
        // 1. Transaction on 31 Dec 2025 (Revenue)
        $journal = $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'journal_date' => '2025-12-31',
            'reference_no' => 'REV-25-001',
            'description' => 'Last Revenue 2025',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 5000000],
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 5000000, 'credit' => 0],
            ]
        ]));

        // Sync Dec 2025
        $decPeriod = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2025-12')->first();
        $this->glService->syncByPeriod($decPeriod->uuid);

        // Verify Dec 31 Balance
        $reportDec = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->revenueAccount->uuid,
            'branch_uuid'  => $this->branch1->uuid,
            'date_from'    => '2025-12-01',
            'date_to'      => '2025-12-31',
        ]));
        $this->assertEquals(5000000, abs($reportDec['closing_balance']));

        // 2. Transaction on 1 Jan 2026
        $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'journal_date' => '2026-01-01',
            'reference_no' => 'REV-26-001',
            'description' => 'First Revenue 2026',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 2000000],
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 2000000, 'credit' => 0],
            ]
        ]));

        // Sync Jan 2026
        $janPeriod = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2026-01')->first();
        $this->glService->syncByPeriod($janPeriod->uuid);

        // 3. Verify Opening Balance Jan 2026 (Should be 0 for P&L)
        // Let's check opening balance from repository directly to be sure
        $repoOpening = app(GeneralLedgerRepository::class)
            ->getOpeningBalance($this->revenueAccount->uuid, '2026-01-01', $this->branch1->uuid);
            
        $this->assertEquals(0, $repoOpening, "P&L Opening Balance at Year Start should be 0");
    }

    public function test_balance_sheet_accumulation_at_year_end()
    {
        // 1. Transaction on 31 Dec 2025 (Asset)
        $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'journal_date' => '2025-12-31',
            'reference_no' => 'CASH-25-001',
            'description' => 'Last Cash 2025',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 5000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 5000000],
            ]
        ]));

        // Sync Dec 2025
        $decPeriod = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2025-12')->first();
        $this->glService->syncByPeriod($decPeriod->uuid);

        // 2. Verify Opening Balance Jan 2026 (Should carry forward for Asset)
        $repoOpening = app(GeneralLedgerRepository::class)
            ->getOpeningBalance($this->cashAccount->uuid, '2026-01-01', $this->branch1->uuid);
            
        $this->assertEquals(5000000, $repoOpening, "Asset Opening Balance at Year Start should carry forward");
    }

    public function test_consolidated_ledger_report()
    {
        // 1. Transaction in Branch 1
        $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'journal_date' => '2026-01-01',
            'reference_no' => 'B1-001',
            'description' => 'B1 Trans',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 1000000],
            ]
        ]));

        // 2. Transaction in Branch 2
        $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch2->uuid,
            'journal_date' => '2026-01-02',
            'reference_no' => 'B2-001',
            'description' => 'B2 Trans',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 2000000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 2000000],
            ]
        ]));

        // Sync both
        $p1 = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2026-01')->first();
        $p2 = ClosingPeriod::where('branch_uuid', $this->branch2->uuid)->where('period', '2026-01')->first();
        $this->glService->syncByPeriod($p1->uuid);
        $this->glService->syncByPeriod($p2->uuid);

        // 3. Get Consolidated Report (branchId = null)
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->cashAccount->uuid,
            'branch_uuid'  => null,
            'date_from'    => '2026-01-01',
            'date_to'      => '2026-01-31',
        ]));
        
        // Assert total balance across branches
        $transactions = $report['transactions'];
        $this->assertNotEmpty($transactions, "Consolidated report should not be empty");
        
        $this->assertEquals(3000000, $report['closing_balance'], "Consolidated balance should be sum of all branches");
    }

    public function test_description_propagation()
    {
        // Create journal with description but NO line description
        $this->journalService->upsertJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch1->uuid,
            'journal_date' => '2026-01-10',
            'reference_no' => 'DESC-001',
            'description' => 'Electricity Payment Jan 2026',
            'reference_type' => JournalReferenceType::GENERAL, // dummy
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 150000],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 150000, 'credit' => 0],
            ]
        ]));

        // Sync
        $janPeriod = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2026-01')->first();
        $this->glService->syncByPeriod($janPeriod->uuid);

        // Verify GL description
        $glEntry = GeneralLedger::where('reference_no', 'DESC-001')
            ->where('account_uuid', $this->cashAccount->uuid)
            ->first();
            
        $this->assertEquals('Electricity Payment Jan 2026', $glEntry->description, "GL should inherit description from Journal header");
    }
}
