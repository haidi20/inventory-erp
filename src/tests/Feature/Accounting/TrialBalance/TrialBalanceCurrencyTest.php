<?php

namespace Tests\Feature\Accounting\TrialBalance;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Skenario 11: Validasi Mata Uang Asing dan Selisih Kurs
 */
class TrialBalanceCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected TrialBalanceService $trialBalanceService;
    protected Branch $branch;
    protected ClosingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->trialBalanceService = app(TrialBalanceService::class);

        $this->branch = Branch::factory()->create([
            'name' => 'Currency Branch', 'short_name' => 'CUR', 'is_active' => true
        ]);
        $this->period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid, 'period' => '2026-03', 'name' => 'Maret 2026',
            'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open'
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * 11.1: Konversi kurs - transaksi valas tercatat dalam IDR dan foreign_amount
     */
    public function test_foreign_currency_transaction_records_correctly()
    {
        $accKasUSD = Account::create([
            'code' => '1102', 'name' => 'Kas USD', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $accPendapatan = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);

        // Transaksi USD 1,000 @ kurs 15,500 = IDR 15,500,000
        $usdAmount = 1000;
        $exchangeRate = 15500;
        $idrAmount = $usdAmount * $exchangeRate;

        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FX-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Pendapatan USD',
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => $idrAmount, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => $usdAmount, 'exchange_rate' => $exchangeRate],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => $idrAmount],
            ],
        ]));

        // Verifikasi journal_line memiliki data valas
        $line = JournalLine::where('account_uuid', $accKasUSD->uuid)->first();
        $this->assertEquals('USD', $line->foreign_currency);
        $this->assertEquals($usdAmount, (float) $line->foreign_amount);
        $this->assertEquals($exchangeRate, (float) $line->exchange_rate);

        // Verifikasi TB tetap balance dalam IDR
        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);
        $this->assertTrue($tb->is_balanced, 'TB harus balance dalam IDR');
        $this->assertEquals($idrAmount, $tb->totals->total_debit);
    }

    /**
     * 11.2: Realized Gain/Loss - selisih kurs saat pembayaran piutang valas
     */
    public function test_realized_exchange_gain_on_payment()
    {
        $accPiutang = Account::create([
            'code' => '1201', 'name' => 'Piutang USD', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $accKas = Account::create([
            'code' => '1101', 'name' => 'Kas IDR', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $accGainLoss = Account::create([
            'code' => '8101', 'name' => 'Selisih Kurs', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);
        $accPendapatan = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);

        // 1. Catat piutang USD 10,000 @ kurs 15,000
        $kursAwal = 15000;
        $usd = 10000;
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FX-PIU-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accPiutang->uuid, 'debit' => $usd * $kursAwal, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => $usd, 'exchange_rate' => $kursAwal],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => $usd * $kursAwal],
            ],
        ]));

        // 2. Terima pembayaran @ kurs 15,500 → gain Rp 5,000,000
        $kursBayar = 15500;
        $selisih = ($kursBayar - $kursAwal) * $usd; // 5,000,000 gain
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FX-PAY-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-03-20',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => $usd * $kursBayar, 'credit' => 0],
                ['account_uuid' => $accPiutang->uuid, 'debit' => 0, 'credit' => $usd * $kursAwal],
                ['account_uuid' => $accGainLoss->uuid, 'debit' => 0, 'credit' => $selisih],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);

        $this->assertTrue($tb->is_balanced, 'TB harus balance setelah realized gain');

        // Verifikasi akun selisih kurs memiliki saldo
        $glGain = GeneralLedger::where('branch_uuid', $this->branch->uuid)
            ->where('account_uuid', $accGainLoss->uuid)->sum('credit');
        $this->assertEquals($selisih, $glGain, 'Selisih kurs realized harus tercatat');
    }

    /**
     * 11.3: Unrealized Gain/Loss - revaluasi saldo valas akhir bulan
     */
    public function test_unrealized_exchange_gain_revaluation()
    {
        $accKasUSD = Account::create([
            'code' => '1102', 'name' => 'Kas USD', 'type' => 'asset',
            'financial_statement' => 'balance_sheet', 'is_header' => 0
        ]);
        $accUnrealizedGL = Account::create([
            'code' => '8102', 'name' => 'Unrealized Gain/Loss', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);
        $accPendapatan = Account::create([
            'code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue',
            'financial_statement' => 'income_statement', 'is_header' => 0
        ]);

        // 1. Saldo Kas USD 50,000 @ 15,000 = IDR 750,000,000
        $kursAwal = 15000;
        $usd = 50000;
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FX-KAS-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-03-01',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => $usd * $kursAwal, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => $usd, 'exchange_rate' => $kursAwal],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => $usd * $kursAwal],
            ],
        ]));

        // 2. Revaluasi akhir bulan @ kurs 15,800 → unrealized gain = 800 * 50,000 = 40,000,000
        $kursBaru = 15800;
        $unrealizedGain = ($kursBaru - $kursAwal) * $usd;
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FX-REVAL-001',
            'reference_uuid' => 0,
            'journal_date' => '2026-03-31',
            'reference_type' => JournalReferenceType::ADJUSTMENT,
            'description' => 'Revaluasi kurs akhir bulan',
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => $unrealizedGain, 'credit' => 0],
                ['account_uuid' => $accUnrealizedGL->uuid, 'debit' => 0, 'credit' => $unrealizedGain],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);

        $this->assertTrue($tb->is_balanced);
        $kasBalance = collect($tb->accounts)->firstWhere('account_code', '1102');
        $this->assertNotNull($kasBalance);
        $expectedKas = ($usd * $kursAwal) + $unrealizedGain; // 750M + 40M
        $this->assertEquals($expectedKas, $kasBalance->total_debit, 'Kas USD harus merefleksikan revaluasi');
    }

    /**
     * 11.4: Multi-currency pada TB tetap konsisten (total debit=credit dalam IDR)
     */
    public function test_multi_currency_trial_balance_consistency()
    {
        $accKasIDR = Account::create(['code' => '1101', 'name' => 'Kas IDR', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accKasUSD = Account::create(['code' => '1102', 'name' => 'Kas USD', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accKasSGD = Account::create(['code' => '1103', 'name' => 'Kas SGD', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        // Transaksi IDR
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'MC-IDR',
            'reference_uuid' => 0, 'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasIDR->uuid, 'debit' => 10000000, 'credit' => 0],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 10000000],
            ],
        ]));

        // Transaksi USD 1000 @ 15500
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'MC-USD',
            'reference_uuid' => 0, 'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => 15500000, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => 1000, 'exchange_rate' => 15500],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 15500000],
            ],
        ]));

        // Transaksi SGD 2000 @ 11500
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'MC-SGD',
            'reference_uuid' => 0, 'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasSGD->uuid, 'debit' => 23000000, 'credit' => 0,
                 'foreign_currency' => 'SGD', 'foreign_amount' => 2000, 'exchange_rate' => 11500],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 23000000],
            ],
        ]));

        app(GeneralLedgerService::class)->syncByPeriod($this->period->uuid);
        $tb = $this->trialBalanceService->generate($this->period->uuid, $this->branch->uuid, true);

        $this->assertTrue($tb->is_balanced, 'Multi-currency TB harus balance dalam IDR');
        $expectedTotal = 10000000 + 15500000 + 23000000;
        $this->assertEquals($expectedTotal, $tb->totals->total_debit);
    }

    /**
     * 11.5: Verifikasi exchange_rate historis tersimpan per transaksi
     */
    public function test_historical_exchange_rate_preserved()
    {
        $accKasUSD = Account::create(['code' => '1102', 'name' => 'Kas USD', 'type' => 'asset', 'financial_statement' => 'balance_sheet', 'is_header' => 0]);
        $accPendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'financial_statement' => 'income_statement', 'is_header' => 0]);

        // Transaksi 1: kurs 15,000
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'HIST-001',
            'reference_uuid' => 0, 'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => 15000000, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => 1000, 'exchange_rate' => 15000],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 15000000],
            ],
        ]));

        // Transaksi 2: kurs 15,800
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, 'reference_no' => 'HIST-002',
            'reference_uuid' => 0, 'journal_date' => '2026-03-20',
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => 'posted',
            'lines' => [
                ['account_uuid' => $accKasUSD->uuid, 'debit' => 15800000, 'credit' => 0,
                 'foreign_currency' => 'USD', 'foreign_amount' => 1000, 'exchange_rate' => 15800],
                ['account_uuid' => $accPendapatan->uuid, 'debit' => 0, 'credit' => 15800000],
            ],
        ]));

        // Verifikasi kurs historis tersimpan per transaksi
        $lines = JournalLine::where('account_uuid', $accKasUSD->uuid)
            ->where('foreign_currency', 'USD')
            ->orderBy('id')->get();

        $this->assertCount(2, $lines);
        $this->assertEquals(15000, (float) $lines[0]->exchange_rate, 'Kurs historis transaksi 1');
        $this->assertEquals(15800, (float) $lines[1]->exchange_rate, 'Kurs historis transaksi 2');
    }
}
