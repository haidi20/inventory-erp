<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalMultiCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $cashUsdAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->branch = Branch::create([
            'name' => 'Currency Branch',
            'short_name' => 'CUR',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Current Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);

        $this->cashUsdAccount = Account::create([
            'code' => '1102',
            'name' => 'Kas USD',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);

        $this->revenueAccount = Account::create([
            'code' => '4101',
            'name' => 'Pendapatan Jasa',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'financial_statement' => 'income_statement',
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    public function test_usd_sale_journal_persists_foreign_currency_fields(): void
    {
        $journal = $this->journalService->createJournal($this->makeUsdSaleDto(
            'MC-USD-001',
            1000,
            15000
        ));

        $cashLine = $journal->lines->firstWhere('account_uuid', $this->cashUsdAccount->uuid);

        $this->assertNotNull($cashLine);
        $this->assertSame('USD', $cashLine->foreign_currency);
        $this->assertEquals(1000.0, (float) $cashLine->foreign_amount);
        $this->assertEquals(15000.0, (float) $cashLine->exchange_rate);
        $this->assertEquals(15000000.0, (float) $cashLine->debit);
    }

    public function test_idr_amount_matches_foreign_amount_times_exchange_rate(): void
    {
        $foreignAmount = 250;
        $exchangeRate = 16000;
        $expectedIdr = $foreignAmount * $exchangeRate;

        $journal = $this->journalService->createJournal($this->makeUsdSaleDto(
            'MC-USD-002',
            $foreignAmount,
            $exchangeRate
        ));

        $cashLine = $journal->lines->firstWhere('account_uuid', $this->cashUsdAccount->uuid);
        $revenueLine = $journal->lines->firstWhere('account_uuid', $this->revenueAccount->uuid);

        $this->assertEquals($expectedIdr, (float) $cashLine->debit);
        $this->assertEquals($expectedIdr, (float) $revenueLine->credit);
        $this->assertEquals(
            $expectedIdr,
            (float) $cashLine->foreign_amount * (float) $cashLine->exchange_rate
        );
    }

    private function makeUsdSaleDto(string $referenceNo, float $foreignAmount, float $exchangeRate): JournalEntity
    {
        $amountIdr = $foreignAmount * $exchangeRate;

        return JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => $referenceNo,
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Penjualan USD',
            'lines' => [
                [
                    'account_uuid' => $this->cashUsdAccount->uuid,
                    'debit' => $amountIdr,
                    'credit' => 0,
                    'foreign_currency' => 'USD',
                    'foreign_amount' => $foreignAmount,
                    'exchange_rate' => $exchangeRate,
                ],
                [
                    'account_uuid' => $this->revenueAccount->uuid,
                    'debit' => 0,
                    'credit' => $amountIdr,
                ],
            ],
        ]);
    }
}
