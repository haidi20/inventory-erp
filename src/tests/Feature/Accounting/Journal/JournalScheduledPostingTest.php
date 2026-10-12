<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalScheduledPostingTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        $this->service = app(JournalService::class);
        $this->branch = Branch::where('short_name', 'KDM')->first();
        
        $user = User::first();
        $this->actingAs($user);
    }

    /**
     * Skenario 33.1: Jurnal Dijadwalkan (Belum Posting ke Ledger)
     */
    public function test_scheduled_journal_is_not_posted_immediately(): void
    {
        $accKas = Account::where('code', '1102')->first();
        $accRev = Account::where('code', '4101')->first();

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'scheduled_at' => now()->addDay(), // Besok
            'reference_no' => 'SCH-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 5000, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 5000],
            ],
        ]);

        $journal = $this->service->createJournal($dto);

        $this->assertNotNull($journal->scheduled_at);
        $this->assertFalse($journal->is_processed);
        // Cek ledger: Harusnya belum ada saldo
        $ledegBalance = JournalLine::where('account_uuid', $accKas->uuid)->where('is_processed', true)->sum(\DB::raw('debit - credit'));
        $this->assertEquals(0, $ledegBalance);
    }

    /**
     * Skenario 33.2: Eksekusi Posting Terjadwal
     */
    public function test_process_scheduled_journals(): void
    {
        $accKas = Account::where('code', '1102')->first();
        $accRev = Account::where('code', '4101')->first();

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'scheduled_at' => now()->subMinute(), // Sudah lewat
            'reference_no' => 'SCH-PAST-01',
            'reference_type' => JournalReferenceType::GENERAL,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 7500, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 7500],
            ],
        ]);

        $journal = $this->service->createJournal($dto);
        
        $count = $this->service->processScheduledJournals();

        $this->assertEquals(1, $count);
        $this->assertEquals(JournalStatus::POSTED, $journal->fresh()->status);
        $this->assertEquals(JournalWorkflowStatus::APPROVED, $journal->fresh()->workflow_status);
        $this->assertTrue((bool) $journal->fresh()->is_processed);
        
        $ledgerBalance = JournalLine::where('account_uuid', $accKas->uuid)->where('is_processed', true)->sum(\DB::raw('debit - credit'));
        // 7,500 (test) = 7,500 (karena 1102 tidak ada di demo seeder)
        $this->assertEquals(7500.00, (float)$ledgerBalance);
    }
}
