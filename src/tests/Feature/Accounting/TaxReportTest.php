<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * TaxReportTest
 * 
 * Verifikasi laporan Pajak (PPN, PPh 21, PPh 23, dst).
 */
class TaxReportTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branch;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->service = app(FinancialReportService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    public function test_vat_input_output_calculation()
    {
        // 1. Setup Tax accounts
        $vatIn = Account::create(['code' => '1106', 'name' => 'PPN Masukan', 'type' => 'asset', 'is_header' => false]);
        $vatOut = Account::create(['code' => '2106', 'name' => 'PPN Keluaran', 'type' => 'liability', 'is_header' => false]);
        
        // 2. Post VAT Out (Sale 10jt, VAT 1.1jt) - Date 2026-01-15
        $this->postJournal('2026-01-15', [
            ['account_uuid' => Account::factory()->create(['code' => '1101'])->uuid, 'debit' => 11100000, 'credit' => 0],
            ['account_uuid' => Account::factory()->create(['code' => '4101'])->uuid, 'debit' => 0, 'credit' => 10000000],
            ['account_uuid' => $vatOut->uuid, 'debit' => 0, 'credit' => 1100000],
        ]);

        // 3. Post VAT In (Purchase 5jt, VAT 550rb) - Date 2026-01-15
        $this->postJournal('2026-01-15', [
            ['account_uuid' => Account::factory()->create(['code' => '5101'])->uuid, 'debit' => 5000000, 'credit' => 0],
            ['account_uuid' => $vatIn->uuid, 'debit' => 550000, 'credit' => 0],
            ['account_uuid' => Account::factory()->create(['code' => '1102'])->uuid, 'debit' => 0, 'credit' => 5550000],
        ]);

        $result = $this->service->getTaxReport($this->period->uuid, $this->branch->uuid);
        
        $vatOutRow = collect($result['details'])->firstWhere('tax_type', 'PPN Keluaran');
        $this->assertNotNull($vatOutRow);
        $this->assertEquals(-1100000, $vatOutRow->tax_amount); // Credit balance for liability

        $vatInRow = collect($result['details'])->firstWhere('tax_type', 'PPN Masukan');
        $this->assertNotNull($vatInRow);
        $this->assertEquals(550000, $vatInRow->tax_amount);
    }

    private function postJournal(string $date, array $lines)
    {
        app(JournalService::class)->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TR-TAX-' . uniqid(),
            'reference_uuid' => 0,
            'journal_date' => $date,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => $lines,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));
    }
}
