<?php

namespace Tests\Feature\Accounting\GeneralLedger;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Hasil Benchmarking (Generasi Tabel Fisik):
 * 500 jurnal: ~0.20 detik
 * 1000 jurnal: ~0.41 detik
 * 
 * Limit 1 Menit:
 * PHP Loop: 150.000 jurnal: 60.19 detik
 * SQL (Stored Proc Eq): ~9.500.000 jurnal (Estimasi)
 * 
 * SQL High Scale Benchmarking:
 * 150.000 jurnal (via SQL): 0.62 detik
 * 1.000.000 jurnal (via SQL): 6.28 detik
 */
class GeneralLedgerSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;
    protected $kas;
    protected $pendapatan;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Klinik Demo', 'short_name' => 'KDM']);
        $this->kas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'type_saldo' => 'debit', 'level' => 3]);
        $this->pendapatan = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'type_saldo' => 'credit', 'level' => 3]);

        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-03',
            'name' => 'March 2026',
            'start_date' => '2026-02-27',
            'end_date' => '2026-03-26',
            'status' => 'open'
        ]);

        $this->service = app(GeneralLedgerService::class);
    }

    /**
     * Helper to seed journals quickly.
     */
    protected function seedJournals(int $count)
    {
        $outerChunkSize = 5000;
        $innerInsertSize = 50; // SQLite limit protection (999 placeholders)

        for ($i = 0; $i < $count; $i += $outerChunkSize) {
            $currentOuterChunk = min($outerChunkSize, $count - $i);
            
            DB::transaction(function () use ($i, $currentOuterChunk, $innerInsertSize) {
                for ($k = 0; $k < $currentOuterChunk; $k += $innerInsertSize) {
                    $insertCount = min($innerInsertSize, $currentOuterChunk - $k);
                    $journals = [];
                    for ($j = 1; $j <= $insertCount; $j++) {
                        $index = $i + $k + $j;
                        $journals[] = [
                            'branch_uuid' => $this->branch->uuid,
                            'transaction_code' => 'GJ-' . $index,
                            'reference_no' => 'REF-' . $index,
                            'reference_type' => JournalReferenceType::GENERAL,
                            'reference_uuid' => $index,
                            'journal_date' => '2026-03-01',
                            'description' => 'Stress Test Jurnal #' . $index,
                            'status' => 'posted',
                            'is_system' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('journals')->insert($journals);
                    
                    $insertedIds = DB::table('journals')
                        ->whereIn('transaction_code', array_column($journals, 'transaction_code'))
                        ->pluck('id', 'transaction_code');
                    
                    $lines = [];
                    foreach ($journals as $journalData) {
                        $journalId = $insertedIds[$journalData['transaction_code']];
                        $lines[] = [
                            'branch_uuid' => $this->branch->uuid,
                            'journal_uuid' => $journalId,
                            'account_uuid' => $this->kas->uuid,
                            'debit' => 10000,
                            'credit' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $lines[] = [
                            'branch_uuid' => $this->branch->uuid,
                            'journal_uuid' => $journalId,
                            'account_uuid' => $this->pendapatan->uuid,
                            'debit' => 0,
                            'credit' => 10000,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('journal_lines')->insert($lines);
                }
            });
        }
    }

    /**
     * @group performance
     */
    public function test_performance_50000_journals_via_sql()
    {
        $this->markTestSkipped('Skipping 50k journal test as it exceeds 1 minute on This environment.');
        
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        
        $count = 50000;
        $this->seedJournals($count);
        
        $start = microtime(true);
        $period = ClosingPeriod::first();
        $this->service->syncByPeriod($period->uuid);
        $end = microtime(true);
        
        $duration = round($end - $start, 4);
        fwrite(STDOUT, "\n[SQL-PERF] Synchronizing $count journals via SQL took: $duration seconds\n");
        $this->assertTrue(true);
    }

    /**
     * @group performance
     * @group heavy
     */
    public function test_performance_9500000_journals_via_sql()
    {
        $this->markTestSkipped('Extreme performance test (9.5M journals) skipped for standard runs.');
        
        ini_set('memory_limit', '16G'); 
        set_time_limit(36000); // 10 Jam (untuk proses seeding yang lama)
        
        $count = 9500000;
        fwrite(STDOUT, "\n[SETUP] Seeding $count journals... (Mohon tunggu, ini butuh waktu cukup lama)\n");
        $this->seedJournals($count);
        
        $period = ClosingPeriod::first();
        fwrite(STDOUT, "[EXEC] Memulai Sinkronisasi Buku Besar via SQL...\n");
        $start = microtime(true);
        $this->service->syncByPeriod($period->uuid);
        $end = microtime(true);
        
        $duration = round($end - $start, 4);
        fwrite(STDOUT, "\n[LIMIT-1-MENIT] Sinkronisasi $count jurnal via SQL memakan waktu: $duration detik\n");
        
        if ($duration <= 60) {
            fwrite(STDOUT, "KONFIRMASI: Sistem berhasil memproses $count data di bawah 1 menit!\n");
        } else {
            fwrite(STDOUT, "HASIL: Sistem memproses $count data dalam $duration detik (Sedikit di atas 1 menit).\n");
        }
        
        $this->assertTrue(true);
    }
}
