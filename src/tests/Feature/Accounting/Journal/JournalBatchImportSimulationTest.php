<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalBatchImportSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;
    protected Account $accKas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Batch Branch', 'short_name' => 'BAT', 'is_active' => true]);
        $this->accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit']);

        ClosingPeriod::create(['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m'), 'name' => 'P-Batch', 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
    }

    /**
     * Skenario 29: Simulasi Import Batch Besar (100+ Transaksi)
     */
    public function test_large_batch_import_performance_and_integrity(): void
    {
        $batchSize = 100;
        $referencePrefix = 'BATCH-' . uniqid();
        
        // 1. Execute Batch Loop
        for ($i = 1; $i <= $batchSize; $i++) {
            $dto = JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid,
                'journal_date' => now()->toDateString(),
                'reference_no' => "{$referencePrefix}-{$i}",
                'reference_type' => JournalReferenceType::GENERAL,
                'description' => "Batch Import Item #{$i}",
                'lines' => [
                    ['account_uuid' => $this->accKas->uuid, 'debit' => 1000, 'credit' => 0],
                    ['account_uuid' => $this->accKas->uuid, 'debit' => 0, 'credit' => 1000],
                ],
            ]);

            $this->service->createJournal($dto);
        }

        // 2. Verification
        $journalCount = Journal::where('branch_uuid', $this->branch->uuid)->count();
        $this->assertEquals($batchSize, $journalCount, "Harusnya ada {$batchSize} jurnal tersimpan.");

        $period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->firstOrFail();
        app(GeneralLedgerService::class)->syncByPeriod($period->uuid);

        // Check transaction code uniqueness
        $uniqueCodes = Journal::where('branch_uuid', $this->branch->uuid)->distinct()->count('transaction_code');
        $this->assertEquals($batchSize, $uniqueCodes, "Setiap jurnal harus memiliki transaction_code unik.");

        // Check ledger
        $ledgerCount = GeneralLedger::where('branch_uuid', $this->branch->uuid)->count();
        $this->assertEquals($batchSize * 2, $ledgerCount, "Harusnya ada " . ($batchSize * 2) . " baris buku besar (2 per jurnal).");
    }
}
