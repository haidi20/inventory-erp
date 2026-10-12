<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Finance\Journal\Services\TransactionCodeService;
use App\Modules\Finance\Journal\Models\TransactionSequence;

class TransactionCodeConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionCodeService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionCodeService::class);
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'short_name' => 'TST',
            'is_active' => true,
        ]);
    }

    /**
     * Skenario 24: Verifikasi keunikan kode transaksi dalam simulasi serial yang rapat.
     * Catatan: PHPUnit berjalan singe-threaded, namun kita bisa mengetes logika increment-nya.
     */
    public function test_transaction_code_is_sequential_and_unique(): void
    {
        $codes = [];
        $count = 50;

        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->service->generate($this->branch->uuid, JournalReferenceType::SALE);
        }

        // 1. Cek jumlah kode yang dihasilkan
        $this->assertCount($count, $codes);

        // 2. Cek keunikan
        $uniqueCodes = array_unique($codes);
        $this->assertCount($count, $uniqueCodes, "Ditemukan duplikat pada kode transaksi!");

        // 3. Cek format sequential (misal index 0001, 0002, ...)
        $yearMonth = now()->format('y') . now()->format('m');
        $this->assertEquals("BKM/TST/{$yearMonth}/0001", $codes[0]);
        $this->assertEquals("BKM/TST/{$yearMonth}/0050", $codes[49]);
    }

    /**
     * Simulasi locking (manual check logic)
     */
    public function test_concurrent_simulation_logic(): void
    {
        // Kita tidak bisa simulasi thread asli di PHPUnit biasa tanpa pthreads/parallel extension,
        // tapi kita bisa verifikasi bahwa state di DB terupdate dengan benar.
        
        $this->service->generate($this->branch->uuid, JournalReferenceType::PURCHASE);
        
        $sequence = TransactionSequence::where('branch_uuid', $this->branch->uuid)
            ->where('type_code', 'BKK')
            ->first();
            
        $this->assertNotNull($sequence);
        $this->assertEquals(1, $sequence->last_index);
    }
}
