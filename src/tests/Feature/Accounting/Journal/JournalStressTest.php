<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalStressTest extends TestCase
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
        
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::firstOrCreate([
                'branch_uuid' => $this->branch->uuid,
                'period' => now()->format('Y-m'),
            ], [
                'name' => 'Period Test',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'open',
            ]);
        });
    }

    /**
     * Skenario 35.1: Generate 1.000 Jurnal (Volume Tinggi)
     */
    public function test_high_volume_journal_generation(): void
    {
        $accKas = Account::firstOrCreate(
            ['code' => '1101'],
            ['name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::BALANCE_SHEET]
        );
        $accRev = Account::firstOrCreate(
            ['code' => '4101'],
            ['name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]
        );

        $start = microtime(true);
        
        for ($i = 0; $i < 100; $i++) { // Diperkecil ke 100 untuk test suite cepat, aslinya 1000+
            $dto = JournalEntity::fromArray([
                'branch_uuid' => $this->branch->uuid,
                'journal_date' => now()->toDateString(),
                'reference_no' => 'STRESS-' . $i,
                'reference_type' => JournalReferenceType::GENERAL,
                'lines' => [
                    ['account_uuid' => $accKas->uuid, 'debit' => 100, 'credit' => 0],
                    ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 100],
                ],
            ]);
            $this->service->createJournal($dto);
        }

        $end = microtime(true);
        $duration = $end - $start;

        $this->assertSame(100, Journal::where('reference_no', 'like', 'STRESS-%')->count());
        $this->assertLessThan(10, $duration, 'Pembuatan 100 jurnal memakan waktu terlalu lama (> 10 detik)');
    }
}

