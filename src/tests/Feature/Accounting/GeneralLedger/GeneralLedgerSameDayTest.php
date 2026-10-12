<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Database\Seeders\V1\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralLedgerSameDayTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected GeneralLedgerService $glService;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->journalService = app(JournalService::class);
        $this->glService = app(GeneralLedgerService::class);

        // Setup Branch dari Seeder
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        // Setup Akun dari Seeder
        $this->cashAccount = Account::where('code', '1101')->firstOrFail(); // Kas
        $this->revenueAccount = Account::where('code', '4101')->firstOrFail(); // Pendapatan

        // Setup Periode Akuntansi
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => '2026-02'],
                [
                    'name' => "February 2026",
                    'status' => 'open',
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-02-28',
                ]
            );
        });
    }

    /**
     * @test
     * Skenario 1.3: Running Balance dengan Urutan Transaksi Sama Tanggal
     */
    public function test_running_balance_same_date_order_by_id()
    {
        $branchId = $this->branch->uuid;
        $date = '2026-02-15';

        // 1. Buat 3 Jurnal pada tanggal yang sama secara berurutan
        // Jurnal 1: Debit Rp 100.000
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'    => $branchId,
            'reference_no'   => 'SD-001',
            'journal_date'   => $date,
            'description'    => 'Transaksi 1',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 100000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 100000],
            ]
        ]));

        // Jurnal 2: Debit Rp 50.000
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'    => $branchId,
            'reference_no'   => 'SD-002',
            'journal_date'   => $date,
            'description'    => 'Transaksi 2',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 50000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 50000],
            ]
        ]));

        // Jurnal 3: Kredit Rp 75.000
        $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid'    => $branchId,
            'reference_no'   => 'SD-003',
            'journal_date'   => $date,
            'description'    => 'Transaksi 3',
            'reference_type' => JournalReferenceType::GENERAL,
            'status'         => JournalStatus::POSTED,
            'workflow_status'=> JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 0, 'credit' => 75000],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 75000, 'credit' => 0],
            ]
        ]));

        // 2. Sinkronkan ke General Ledger via service (SQLite tidak mendukung CALL stored procedure)
        $period = ClosingPeriod::where('branch_uuid', $branchId)->where('period', '2026-02')->firstOrFail();
        $this->glService->syncByPeriod($period->uuid);

        // 3. Ambil laporan Buku Besar
        $report = $this->glService->getLedgerReport(GeneralLedgerFilterEntity::fromArray([
            'account_uuid' => $this->cashAccount->uuid,
            'branch_uuid'  => $branchId,
            'date_from'    => '2026-02-01',
            'date_to'      => '2026-02-28',
        ]));

        // Verifikasi ada 3 transaksi
        $this->assertCount(3, $report['transactions']);

        // Akurasi running balance:
        // Transaksi 1: index 0 -> Debit 100.000 -> Bal: 100.000
        // Transaksi 2: index 1 -> Debit 50.000  -> Bal: 150.000
        // Transaksi 3: index 2 -> Credit 75.000 -> Bal: 75.000

        $txs = $report['transactions'];
        $base = $report['opening_balance'];

        $this->assertEquals(100000, $txs[0]['debit']);
        $this->assertEquals($base + 100000, $txs[0]['balance']);

        $this->assertEquals(50000, $txs[1]['debit']);
        $this->assertEquals($base + 150000, $txs[1]['balance']);

        $this->assertEquals(75000, $txs[2]['credit']);
        $this->assertEquals($base + 75000, $txs[2]['balance']);
    }
}

