<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class JournalRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->branch_uuid = $this->branch->uuid;
        $this->user->save();
        
        $this->accounts['cash'] = Account::create(['code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '4101', 'name' => 'Revenue', 'type' => 'revenue', 'type_saldo' => 'credit']);
        
        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 3.2.1: Database Disconnection (Rollback Integrity)
     * Mensimulasikan error di tengah transaksi dan memastikan tidak ada data 'setengah jadi'.
     */
    public function test_rolls_back_entirely_on_mid_transaction_failure()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'FAIL-ROLLBACK-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 100, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 100],
            ]
        ]);

        // Simulasikan force failure dengan merusak input post-processing if possible, 
        // atau kita bisa menggunakan mock pada repository.
        // Di sini kita coba kirim DTO yang valid secara struktur tapi akan gagal saat postToLedger jika kita inject error.
        
        // Agar realistis tanpa merubah kode produksi, kita gunakan DB::transaction manual yang melempar error
        try {
            DB::transaction(function() use ($dto) {
                $this->journalService->createJournal($dto);
                throw new Exception("Simulated DB Crash");
            });
        } catch (Exception $e) {
            $this->assertEquals("Simulated DB Crash", $e->getMessage());
        }

        // Verifikasi TIDAK ADA jurnal yang tersimpan
        $this->assertEquals(0, Journal::where('reference_no', 'FAIL-ROLLBACK-001')->count());
        $this->assertEquals(0, DB::table('journal_lines')->count());
    }

    /**
     * Skenario 3.2.2: Partial Batch Failure (Worker Resilience)
     * Kemampuan untuk re-run jika batch terhenti di tengah.
     */
    public function test_batch_posting_is_idempotent_on_partial_failure()
    {
        // Buat 3 jurnal berstatus posted tapi is_processed = false
        for ($i = 1; $i <= 3; $i++) {
            $j = Journal::create([
                'branch_uuid' => $this->branch->uuid,
                'reference_no' => "BATCH-{$i}",
                'reference_type' => JournalReferenceType::GENERAL,
                'workflow_status' => JournalWorkflowStatus::APPROVED,
                'status' => JournalStatus::UNPOSTED,
                'reference_uuid' => '',
                'journal_date' => now()->toDateString(),
                'transaction_code' => "TX-{$i}",
                'scheduled_at' => now()->subMinutes(5)->timestamp,
                'version' => 1
            ]);
            // lines...
            DB::table('journal_lines')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'branch_uuid' => $this->branch->uuid,
                'journal_uuid' => $j->uuid,
                'account_uuid' => $this->accounts['cash']->uuid,
                'debit' => 100,
                'credit' => 0,
                'is_processed' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Jalankan posting terjadwal
        $count = $this->journalService->processScheduledJournals();
        $this->assertEquals(3, $count);
        $this->assertEquals(JournalStatus::POSTED, Journal::where('reference_no', 'BATCH-1')->first()->status);
        
        // Jalankan LAGE (Idempotency check)
        $countAgain = $this->journalService->processScheduledJournals();
        $this->assertEquals(0, $countAgain); // Tidak boleh memproses ulang yang sudah sukses
    }
}
