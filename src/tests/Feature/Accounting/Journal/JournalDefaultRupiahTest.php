<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalDefaultRupiahTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Main Branch', 'short_name' => 'MN', 'is_active' => true]);
        
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Period Test',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);
    }

    /**
     * Skenario: Transaksi Rupiah Default (Tanpa Valas)
     */
    public function test_journal_with_default_rupiah(): void
    {
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::BALANCE_SHEET]);
        $accRev = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);

        $amount = 1500000;

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'IDR-SALE-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                [
                    'account_uuid' => $accKas->uuid,
                    'debit' => $amount,
                    'credit' => 0,
                    // foreign fields empty
                ],
                [
                    'account_uuid' => $accRev->uuid,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ],
        ]);

        $journal = $this->service->createJournal($dto);
        $lines = collect($journal->lines);
        $firstLine = $lines->first();

        $this->assertNotNull($journal);
        $this->assertEquals($amount, $lines->sum('debit'));
        $this->assertNull($firstLine->foreign_currency);
        $this->assertEquals(0, $firstLine->foreign_amount);
    }
}
