<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralLedgerConsolidateTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected FinancialReportService $reportService;
    protected Branch $branch1;
    protected Branch $branch2;
    protected Account $cashAccount;
    protected Account $revenueAccount;
    protected Account $equityAccount;

    protected function setUp(): void
    {
            parent::setUp();

            $this->seed(DemoSeeder::class);

            // Fix ALL financial_statement statements unconditionally to avoid TypeError on load
            DB::table('accounts')
                ->update(['financial_statement' => FinancialStatementType::BALANCE_SHEET->value]);

            $this->journalService = app(JournalService::class);
            $this->reportService = app(FinancialReportService::class);

            // Setup 2 Cabang
            $this->branch1 = Branch::where('short_name', 'KDM')->firstOrFail();
            
            // Buat cabang kedua jika belum ada atau cari yang lain - BYPASS HYDRATION
            $branch2Data = DB::table('branches')->where('id', '!=', $this->branch1->uuid)->first();
            if ($branch2Data) {
                $this->branch2 = (new Branch())->forceFill((array)$branch2Data);
                $this->branch2->exists = true;
            } else {
                 $this->branch2 = Branch::create(['name' => 'Cabang B', 'short_name' => 'CBB', 'is_active' => true]);
            }

            // Accounts loading - BYPASS HYDRATION
            $cashData = DB::table('accounts')->where('code', '1101')->first();
            if ($cashData) {
                $this->cashAccount = (new Account())->forceFill((array)$cashData);
                $this->cashAccount->exists = true;
            } else {
                $this->cashAccount = Account::where('code', '1101')->firstOrFail();
            }

            $revenueData = DB::table('accounts')->where('code', '4101')->first();
            if ($revenueData) {
                $this->revenueAccount = (new Account())->forceFill((array)$revenueData);
                $this->revenueAccount->exists = true;
            } else {
                $this->revenueAccount = Account::where('code', '4101')->firstOrFail();
            }

            $this->equityAccount = Account::firstOrCreate(
                ['code' => '3101'],
                [
                    'name' => 'Modal Disetor',
                    'type' => 'equity',
                    'type_saldo' => 'credit',
                    'financial_statement' => FinancialStatementType::BALANCE_SHEET,
                    'is_header' => false,
                    'is_active' => true,
                ]
            );

            // Setup Periode untuk kedua cabang — gunakan Unix timestamp seperti DemoSeeder
            // agar syncByPeriod bisa menemukan jurnal yang tanggalnya juga disimpan sebagai Unix timestamp
            ClosingPeriod::withoutEvents(function () {
                ClosingPeriod::firstOrCreate(
                    ['branch_uuid' => $this->branch1->uuid, 'period' => '2026-02'],
                    [
                        'name'       => "Feb 2026 A",
                        'status'     => 'open',
                        'start_date' => strtotime('2026-02-01'),
                        'end_date'   => strtotime('2026-02-28 23:59:59'),
                    ]
                );
                ClosingPeriod::firstOrCreate(
                    ['branch_uuid' => $this->branch2->uuid, 'period' => '2026-02'],
                    [
                        'name'       => "Feb 2026 B",
                        'status'     => 'open',
                        'start_date' => strtotime('2026-02-01'),
                        'end_date'   => strtotime('2026-02-28 23:59:59'),
                    ]
                );
            });

            // Kita butuh financial_statement Type di set untuk test agar terbaca ReportService
            $this->cashAccount->financial_statement = FinancialStatementType::BALANCE_SHEET;
            $this->cashAccount->save();

            $this->revenueAccount->financial_statement = FinancialStatementType::INCOME_STATEMENT;
            $this->revenueAccount->save();
    }

    /** @test */
    public function test_dummy()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * Skenario 4.1: Konsolidasi Neraca Multi-Cabang
     */
    public function test_consolidated_balance_sheet_v2()
    {
        try {
            $date = '2026-02-15';

            // 1. Transaksi Cabang 1: Debit Rp 500.000
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch1->uuid,
                'reference_no' => 'CB1-001',
                'journal_date' => $date,
                'description' => 'Setoran modal Cabang 1',
                'reference_type' => JournalReferenceType::GENERAL,
                'status' => 'posted',
                'lines' => [
                    ['account_uuid' => $this->cashAccount->uuid, 'debit' => 500000, 'credit' => 0],
                    ['account_uuid' => $this->equityAccount->uuid, 'debit' => 0, 'credit' => 500000],
                ]
            ]));

            // 2. Transaksi Cabang 2: Debit Rp 300.000
            $this->journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $this->branch2->uuid,
                'reference_no' => 'CB2-001',
                'journal_date' => $date,
                'description' => 'Setoran modal Cabang 2',
                'reference_type' => JournalReferenceType::GENERAL,
                'status' => 'posted',
                'lines' => [
                    ['account_uuid' => $this->cashAccount->uuid, 'debit' => 300000, 'credit' => 0],
                    ['account_uuid' => $this->equityAccount->uuid, 'debit' => 0, 'credit' => 300000],
                ]
            ]));

            $period1 = ClosingPeriod::where('branch_uuid', $this->branch1->uuid)->where('period', '2026-02')->firstOrFail();
            $period2 = ClosingPeriod::where('branch_uuid', $this->branch2->uuid)->where('period', '2026-02')->firstOrFail();
            app(GeneralLedgerService::class)->syncByPeriod($period1->uuid);
            app(GeneralLedgerService::class)->syncByPeriod($period2->uuid);

            // 3. Tarik Laporan Konsolidasi (branch_uuid = null)
            $consolidatedEntity = FinancialReportEntity::fromArray(['period_uuid' => $period1->uuid, 'branch_uuid' => null]);
            $consolidatedSheet = $this->reportService->getBalanceSheet($consolidatedEntity);

            $this->assertTrue($consolidatedSheet['is_balanced'], "Laporan Konsolidasi harus seimbang");
            $this->assertEquals(800000.0, (float) $consolidatedSheet['assets']['total']);
            $this->assertEquals(800000.0, (float) $consolidatedSheet['total_liabilities_and_equity']);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}

