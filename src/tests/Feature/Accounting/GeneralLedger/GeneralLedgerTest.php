<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected GeneralLedgerService $glService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock "now" to January 2026 so seeder journals are in the past
        Carbon::setTestNow('2026-01-01');

        $this->seed(DemoSeeder::class);

        // Fix for DemoSeeder dates (it uses now() for closing periods too)
        // We'll keep it fixed at March 2026 for the test logic
        Carbon::setTestNow('2026-03-24');

        $this->journalService = app(JournalService::class);
        $this->glService = app(GeneralLedgerService::class);
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        // Ensure periods exist for Feb & March 2026 — use withoutEvents to bypass gap validation
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-02'],
                [
                    'name' => 'February 2026',
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-02-28',
                    'status' => 'open',
                ]
            );
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-03'],
                [
                    'name' => 'March 2026',
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-03-31',
                    'status' => 'open',
                ]
            );
        });
    }

    public function test_general_ledger_report_calculation(): void
    {
        $account = Account::where('code', '1101')->first(); // Kas Besar (Debit)
        $branchId = $this->branch->uuid;

        // 1. Post Journal 1 (Debit Kas 100k) - Tanggal 2026-03-01
        try {
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid'     => $branchId,
                'reference_no'    => 'TEST-001',
                'reference_uuid'  => 1,
                'journal_date'    => '2026-03-01',
                'reference_type'  => JournalReferenceType::GENERAL,
                'description'     => 'Test Transaction 1',
                'status'          => JournalStatus::POSTED,
                'workflow_status' => JournalWorkflowStatus::APPROVED,
                'lines' => [
                    ['account_uuid' => $account->uuid, 'debit' => 100000, 'credit' => 0],
                    ['account_uuid' => Account::where('code', '4101')->first()->uuid, 'debit' => 0, 'credit' => 100000],
                ]
            ]));
        } catch (Exception $e) {
            $this->fail("Failed at Journal 1: " . $e->getMessage());
        }

        // 2. Post Journal 2 (Credit Kas 30k) - Tanggal 2026-03-15
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'     => $branchId,
            'reference_no'    => 'TEST-002',
            'reference_uuid'  => 2,
            'journal_date'    => '2026-03-15',
            'reference_type'  => JournalReferenceType::GENERAL,
            'description'     => 'Test Transaction 2',
            'status'          => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $account->uuid, 'debit' => 0, 'credit' => 30000],
                ['account_uuid' => Account::where('code', '5201')->first()->uuid, 'debit' => 30000, 'credit' => 0],
            ]
        ]));

        // 3. Get Ledger Report for March
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $account->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-03-01',
            'date_to'      => '2026-03-31',
        ]));

        // DemoSeeder sets 1101 to 100,000,000 basic (Opening balance in account_balances)
        $this->assertEquals(100000000, (float) $report['opening_balance']);
        $this->assertCount(2, $report['transactions']);

        // Final balance: 100,000,000 + 100,000 - 30,000 = 100,070,000
        $this->assertEquals(100070000, (float) $report['closing_balance'], "Actual Closing Balance: " . $report['closing_balance']);

        $this->assertEquals(100100000, (float) $report['transactions'][0]['balance']);
        $this->assertEquals(100070000, (float) $report['transactions'][1]['balance']);
    }

    public function test_opening_balance_calculation_before_period(): void
    {
         $account = Account::where('code', '1101')->first();
         $branchId = $this->branch->uuid;

         // Transaction in Feb
         $this->journalService->createJournal(JournalEntity::fromArray([
             'branch_uuid'     => $branchId,
             'reference_no'    => 'TEST-FEB',
             'reference_uuid'  => 99,
             'journal_date'    => '2026-02-15',
             'reference_type'  => JournalReferenceType::GENERAL,
             'description'     => 'Feb Transaction',
             'status'          => JournalStatus::POSTED,
             'workflow_status' => JournalWorkflowStatus::APPROVED,
             'lines' => [
                 ['account_uuid' => $account->uuid, 'debit' => 500000, 'credit' => 0],
                 ['account_uuid' => Account::where('code', '4101')->first()->uuid, 'debit' => 0, 'credit' => 500000],
             ]
         ]));

         // Request report for March
         $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
             'account_uuid' => $account->uuid,
             'branch_uuid'  => $branchId,
             'date_from'    => '2026-03-01',
             'date_to'      => '2026-03-31',
         ]));

         // Opening should be 100,000,000 (base) + 500,000 (Feb transaction) = 100,500,000
         $this->assertEquals(100500000, (float) $report['opening_balance'], "Actual Opening Balance: " . $report['opening_balance']);
    }
}
