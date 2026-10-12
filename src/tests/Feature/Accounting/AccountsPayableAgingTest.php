<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Operational\Master\Models\Supplier;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * AccountsPayableAgingTest
 * 
 * Verifikasi laporan Utang Usaha per umur (Aging).
 */
class AccountsPayableAgingTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-01',
            'start_date' => strtotime('2026-01-01'),
            'end_date' => strtotime('2026-01-31'),
            'status' => 'open'
        ]);
        $this->service = app(FinancialReportService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    public function test_ap_aging_buckets_logic()
    {
        // 1. Setup AP account & Supplier (Vendor)
        $apAccount = Account::create(['code' => '2101', 'name' => 'Utang Usaha', 'type' => 'liability', 'is_header' => false]);
        $supplier = Supplier::create(['name' => 'Supplier A', 'branch_uuid' => $this->branch->uuid]);
        
        // 2. Post AP Journal (0-30 days) - Date 2026-01-15
        $this->postJournal('2026-01-15', [
            ['account_uuid' => Account::factory()->create(['code' => '1101'])->uuid, 'debit' => 1000000, 'credit' => 0],
            ['account_uuid' => $apAccount->uuid, 'debit' => 0, 'credit' => 1000000],
        ], $supplier->uuid);

        // 3. Post AP Journal (Over 60 days from 2026-03)
        $this->postJournal('2026-01-01', [
            ['account_uuid' => Account::factory()->create(['code' => '1102'])->uuid, 'debit' => 2000000, 'credit' => 0],
            ['account_uuid' => $apAccount->uuid, 'debit' => 0, 'credit' => 2000000],
        ], $supplier->uuid);
        
        $result = $this->service->getAccountsPayableAging(new FinancialReportEntity(
            branch_uuid: $this->branch->uuid,
            as_of_date: strtotime('2026-03-01'),
        ));
        
        $details = collect($result['details'])->firstWhere('vendor_name', 'Supplier A');
        $this->assertNotNull($details);
        $this->assertEquals(3000000, $details->total_balance);
    }

    private function postJournal(string $date, array $lines, string $supplierUuid)
    {
        app(JournalService::class)->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-AP-' . uniqid(),
            'reference_uuid' => $supplierUuid,
            'journal_date' => $date,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => $lines,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));
    }
}
