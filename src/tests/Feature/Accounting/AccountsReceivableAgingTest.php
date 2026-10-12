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
use App\Modules\Clinical\Registration\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * AccountsReceivableAgingTest
 * 
 * Verifikasi laporan Piutang Usaha per umur (Aging).
 */
class AccountsReceivableAgingTest extends TestCase
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

    public function test_ar_aging_buckets_logic()
    {
        // 1. Setup AR account & Patient (Customer)
        $arAccount = Account::create(['code' => '1201', 'name' => 'Piutang Usaha', 'type' => 'asset', 'is_header' => false]);
        $patient = Patient::create([
            'name' => 'Patient A', 
            'branch_uuid' => $this->branch->uuid,
            'medical_record_no' => 'MRN-' . rand(1000, 9999)
        ]);
        
        // 2. Post AR Journal (0-30 days) - Date 2026-01-15
        $this->postJournal('2026-01-15', [
            ['account_uuid' => $arAccount->uuid, 'debit' => 1000000, 'credit' => 0],
            ['account_uuid' => Account::factory()->create(['code' => '4101'])->uuid, 'debit' => 0, 'credit' => 1000000],
        ], $patient->uuid);

        // 3. Post AR Journal (Normally this would be old, but for test within period we use start of month)
        // Note: AR Aging usually looks back from CURDATE(). Since we are in 2026-03, 2026-01 is > 60 days.
        $this->postJournal('2026-01-01', [
            ['account_uuid' => $arAccount->uuid, 'debit' => 2000000, 'credit' => 0],
            ['account_uuid' => Account::factory()->create(['code' => '4102'])->uuid, 'debit' => 0, 'credit' => 2000000],
        ], $patient->uuid);
        
        $result = $this->service->getAccountsReceivableAging(new FinancialReportEntity(
            branch_uuid: $this->branch->uuid,
            as_of_date: strtotime('2026-03-01'),
        ));
        
        $details = collect($result['details'])->firstWhere('customer_name', 'Patient A');
        $this->assertNotNull($details, "Hasil aging untuk Patient A tidak ditemukan.");
        $this->assertEquals(3000000, $details->total_balance);
    }

    private function postJournal(string $date, array $lines, string $patientUuid)
    {
        app(JournalService::class)->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-AR-' . uniqid(),
            'reference_uuid' => $patientUuid,
            'journal_date' => $date,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => $lines,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));
    }
}
