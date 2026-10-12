<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\AccountMapping;
use Database\Seeders\V1\DemoSeeder;

class JournalPOSIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        $this->journalService = app(JournalService::class);
        $this->user = User::where('email', 'admin@klinik.local')->first() ?: User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::first();
        
        ClosingPeriod::updateOrCreate(
            ['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m')],
            ['name' => 'P-1', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']
        );
    }

    /**
     * Skenario 2.1.1: POS -> Journal -> GL (Full Flow)
     */
    public function test_pos_sale_generates_correct_journal_lines()
    {
        $sale = Sale::first();
        $this->assertNotNull($sale);
        $sale->load('items.product');

        // Memicu pembuatan jurnal otomatis dari modul POS (Sale)
        $journal = $this->journalService->storeJournal($sale, JournalReferenceType::SALE, [
            'tax_percent' => 11,
            'description' => "Jurnal Penjualan Terintegrasi POS"
        ]);

        $this->assertNotNull($journal);
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
        $this->assertTrue($journal->is_system);
        
        // Verifikasi Balancing
        $this->assertEquals(
            round($journal->lines->sum('debit'), 2), 
            round($journal->lines->sum('credit'), 2)
        );

        // Verifikasi Kas (Debit) sesuai total sale
        $mappings = AccountMapping::where('menu_name', 'Penjualan Obat')->get();
        $kasLine = $journal->lines->where('account_uuid', $mappings->where('key', 'penjualan.kas')->first()->account_uuid)->first();
        $this->assertEquals((float)$sale->total, (float)$kasLine->debit);
    }

    /**
     * Skenario 2.1.2: Encounter -> Journal -> GL
     */
    public function test_medical_encounter_generates_correct_journal_lines()
    {
        $encounter = Encounter::first();
        $this->assertNotNull($encounter);
        $encounter->load('items.product');

        // Memicu pembuatan jurnal otomatis dari modul Rekam Medis (Encounter)
        $journal = $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER, [
            'description' => "Jurnal Layanan Medis Terintegrasi"
        ]);

        $this->assertNotNull($journal);
        $this->assertEquals(JournalStatus::POSTED, $journal->status);
        
        // Verifikasi adanya pendapatan jasa
        $mappings = AccountMapping::where('key', 'pemeriksaan.pendapatan_jasa')->get();
        if ($mappings->isEmpty()) {
            $mappings = AccountMapping::where('key', 'like', 'pemeriksaan%')->get();
        }
        $revenueLine = $journal->lines->where('account_uuid', $mappings->first()?->account_uuid)->first();
        $this->assertNotNull($revenueLine, "Revenue line not found in journal lines");
        $this->assertGreaterThan(0, $revenueLine->credit);
    }
}
