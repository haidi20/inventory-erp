<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Operational\Master\Models\TaxSetting;
use Tests\TestCase;

class JournalTaxValidationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;
    protected Account $accTax;
    protected Account $accExpense;
    protected Account $accKas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Test Branch', 'short_name' => 'TST', 'is_active' => true]);
        
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Period Test',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);

        $this->accTax = Account::create(['code' => '2106', 'name' => 'PPN', 'type' => AccountType::LIABILITY, 'type_saldo' => 'credit']);
        $this->accExpense = Account::create(['code' => '5211', 'name' => 'Beban Umum', 'type' => AccountType::EXPENSE, 'type_saldo' => 'debit']);
        $this->accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit']);

        TaxSetting::create([
            'name' => 'PPN 11%',
            'percentage' => 11.00,
            'account_uuid' => $this->accTax->uuid,
            'is_active' => true
        ]);

        AccountMapping::create([
            'key' => 'biaya.kas_umum',
            'menu_name' => 'Biaya',
            'account_uuid' => $this->accKas->uuid,
        ]);
        
        AccountMapping::create([
            'key' => 'biaya.beban_umum',
            'menu_name' => 'Biaya',
            'account_uuid' => $this->accExpense->uuid,
        ]);
    }

    /**
     * Skenario 26: Verifikasi otomatisasi baris pajak (Automatic Tax Line)
     */
    public function test_automatic_tax_generation_and_balance(): void
    {
        // 1. Prepare DTO: Expense 1,000,000 (taxable 11%) -> Tax 110,000
        //    Total Cash Credit: 1,110,000
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-TAX-' . uniqid(),
            'reference_type' => JournalReferenceType::EXPENSE,
            'description' => 'Test Tax Auto Generation',
            'lines' => [
                [
                    'account_uuid' => $this->accExpense->uuid, 
                    'debit' => 1000000, 
                    'credit' => 0, 
                    'taxable' => true, 
                    'tax_rate' => 11.0
                ],
                [
                    'account_uuid' => $this->accKas->uuid, 
                    'debit' => 0, 
                    'credit' => 1110000, 
                    'taxable' => false
                ],
            ],
        ]);

        // 2. Execution
        $journal = $this->service->createJournal($dto);

        // 3. Assertion
        $this->assertNotNull($journal);
        $this->assertCount(3, $journal->lines, "Harusnya ada 3 baris (Expense, Tax, Kas)");
        
        $taxLine = $journal->lines->where('account_uuid', $this->accTax->uuid)->first();
        $this->assertNotNull($taxLine);
        $this->assertEquals(110000, $taxLine->debit);

        // Pastikan total Debit = total Credit (1,110,000)
        $this->assertEquals(1110000, $journal->lines->sum('debit'));
        $this->assertEquals(1110000, $journal->lines->sum('credit'));
    }
}
