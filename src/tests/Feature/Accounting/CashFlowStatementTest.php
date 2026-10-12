<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Entities\CashFlowEntity;
use App\Modules\Finance\Ledger\Services\CashFlowService;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

/**
 * CashFlowStatementTest
 * 
 * Pengujian komprehensif untuk Laporan Arus Kas (Cash Flow Statement).
 * Mengacu pada panduan 'prompt.md' (Skenario 1-10).
 */
class CashFlowStatementTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected ClosingPeriod $period;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);

        // 1. Setup Cabang dan Periode (Skenario Dasar)
        $this->branch = Branch::factory()->create(['name' => 'Cabang Utama']);
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open'
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        // 2. Setup Akun (Sesuai prompt.md Section 804-809)
        $this->setupAccounts();
    }

    private function setupAccounts(): void
    {
        $map = [
            '1101' => ['name' => 'Kas Besar', 'type' => 'asset'],
            '1102' => ['name' => 'Kas Kecil', 'type' => 'asset'],
            '1201' => ['name' => 'Piutang Usaha', 'type' => 'asset'],
            '1202' => ['name' => 'Persediaan', 'type' => 'asset'],
            '1301' => ['name' => 'Peralatan Medis', 'type' => 'asset'],
            '1302' => ['name' => 'Akumulasi Penyusutan', 'type' => 'asset'],
            '2101' => ['name' => 'Utang Usaha', 'type' => 'liability'],
            '2102' => ['name' => 'Utang Gaji', 'type' => 'liability'],
            '2201' => ['name' => 'Utang Bank', 'type' => 'liability'],
            '3101' => ['name' => 'Modal Pemilik', 'type' => 'equity'],
            '4101' => ['name' => 'Pendapatan Jasa', 'type' => 'revenue'],
            '5101' => ['name' => 'Beban Gaji', 'type' => 'expense'],
            '5102' => ['name' => 'Beban Listrik', 'type' => 'expense'],
            '5103' => ['name' => 'Beban Penyusutan', 'type' => 'expense'],
        ];

        foreach ($map as $code => $data) {
            $this->accounts[$code] = Account::create([
                'code' => $code,
                'name' => $data['name'],
                'type' => $data['type'],
                'is_header' => false,
                'is_active' => true
            ]);
        }
    }

    /**
     * Skenario 1: Arus Kas Operasi - Metode Tidak Langsung
     */
    public function test_operating_cash_flow_indirect_calculation(): void
    {
        $this->setUpJournals();

        $service = app(CashFlowService::class);
        $result = $service->generateIndirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));

        $service = app(CashFlowService::class);
        $result = $service->generateIndirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));

        $this->assertInstanceOf(CashFlowEntity::class, $result);
        // Laba Bersih 47jt + Penyusutan 5jt - Piutang 10jt + Utang 8jt = 50jt
        $this->assertEquals(50000000, $result->operatingTotal, "Arus Kas Operasi harus Rp 50.000.000");
    }

    /**
     * Skenario 3 & 4: Investasi & Pendanaan
     */
    public function test_investing_and_financing_cash_flow(): void
    {
        // Investasi: Beli Alat 50jt (Out)
        $this->postJournal('2026-01-05', [
            ['account_uuid' => $this->accounts['1301']->uuid, 'debit' => 50000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 0, 'credit' => 50000000],
        ]);

        // Pendanaan: Pinjam Bank 100jt (In)
        $this->postJournal('2026-01-08', [
            ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 100000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['2201']->uuid, 'debit' => 0, 'credit' => 100000000],
        ]);

        $service = app(CashFlowService::class);
        $result = $service->generateIndirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));

        $this->assertEquals(-50000000, $result->investingTotal);
        $this->assertEquals(100000000, $result->financingTotal);
    }

    /**
     * Skenario 6: Multi-Cabang (Jakarta & Surabaya)
     */
    public function test_multi_branch_cash_flow(): void
    {
        $branch2 = Branch::factory()->create(['name' => 'Cabang Surabaya']);
        ClosingPeriod::factory()->create([
            'branch_uuid' => $branch2->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open'
        ]);

        // Jakarta: Pendapatan 30jt
        $this->postJournal('2026-01-10', [
            ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 30000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 30000000],
        ]);

        // Surabaya: Pendapatan 20jt
        $user2 = User::factory()->create(['branch_uuid' => $branch2->uuid]);
        $this->actingAs($user2);
        
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $branch2->uuid,
            'reference_no' => 'T-SUB-01',
            'reference_uuid' => 0,
            'journal_date' => '2026-01-11',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 20000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 20000000],
            ],
            'is_system' => false,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));

        // Switch back or act as consolidated user if possible
        $service = app(CashFlowService::class);
        
        $resJKT = $service->generateIndirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));
        $this->assertEquals(30000000, $resJKT->operatingTotal);

        // Konsolidasi (p_branch_id = 0/null)
        $resALL = $service->generateIndirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: null,
        ));
        $this->assertEquals(50000000, $resALL->operatingTotal);
    }

    private function postJournal(string $date, array $lines): void
    {
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'T-' . uniqid(),
            'reference_uuid' => 0,
            'journal_date' => $date,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => $lines,
            'is_system' => false,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
        ]));
    }

    private function setUpJournals(): void
    {
        // 1. Setup Transaksi Laba Rugi (Laba Bersih: 47jt)
        // Pendapatan 100jt (Tunai)
        $this->postJournal('2026-01-10', [
            ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 100000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 100000000],
        ]);
        // Beban Gaji 40jt & Listrik 10jt (Tunai)
        $this->postJournal('2026-01-15', [
            ['account_uuid' => $this->accounts['5101']->uuid, 'debit' => 40000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['5102']->uuid, 'debit' => 10000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 0, 'credit' => 50000000],
        ]);
        // Beban Penyusutan 5jt (Non-kas)
        $this->postJournal('2026-01-31', [
            ['account_uuid' => $this->accounts['5103']->uuid, 'debit' => 5000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['1302']->uuid, 'debit' => 0, 'credit' => 5000000],
        ]);

        // 2. Setup Perubahan Modal Kerja
        // Kenaikan Piutang 10jt
        $this->postJournal('2026-01-20', [
            ['account_uuid' => $this->accounts['1201']->uuid, 'debit' => 10000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 10000000],
        ]);
        // Kenaikan Utang Usaha 5jt & Utang Gaji 3jt
        $this->postJournal('2026-01-22', [
            ['account_uuid' => $this->accounts['5101']->uuid, 'debit' => 8000000, 'credit' => 0],
            ['account_uuid' => $this->accounts['2101']->uuid, 'debit' => 0, 'credit' => 5000000],
            ['account_uuid' => $this->accounts['2102']->uuid, 'debit' => 0, 'credit' => 3000000],
        ]);
    }

    /**
     * Skenario 2: Arus Kas Operasi - Metode Langsung
     */
    public function test_operating_cash_flow_direct_calculation()
    {
        $this->setUpJournals();
        $service = app(CashFlowService::class);
        $result = $service->generateDirectMethod(new FinancialReportEntity(
            period_uuid: $this->period->uuid,
            branch_uuid: $this->branch->uuid,
        ));

        $this->assertInstanceOf(CashFlowEntity::class, $result);
        // Dari Jurnal: Penerimaan 100jt, Pembayaran 50jt
        $this->assertEquals(100000000, $result->operatingActivities[0]['amount'], "Penerimaan harus Rp 100.000.000");
        $this->assertEquals(-50000000, $result->operatingActivities[1]['amount'], "Pembayaran harus Rp (50.000.000)");
        $this->assertEquals(50000000, $result->operatingTotal, "Arus Kas Operasi (Direct) harus Rp 50.000.000");
    }
}
