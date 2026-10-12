<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalTaxTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;
    protected Account $revenueAccount;
    protected Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->journalService = app(JournalService::class);

        // Setup Branch from Seeder
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        $user = User::first();
        $this->actingAs($user);

        // Setup Accounts from Seeder
        $this->revenueAccount = Account::where('type', 'revenue')->where('is_header', false)->firstOrFail();
        $this->cashAccount = Account::where('type', 'asset')->where('is_header', false)->where('code', 'like', '1%')->firstOrFail();

        // Setup Accounting Periods
        ClosingPeriod::withoutEvents(function () {
            ClosingPeriod::where('branch_uuid', $this->branch->uuid)->delete();
            ClosingPeriod::create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->branch->company_uuid,
                'period' => '2026-02',
                'name' => 'Periode 2026-02',
                'status' => 'open',
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-28',
            ]);
        });
    }

    /**
     * @test
     * Skenario 4.1: Hitung PPN Keluaran Otomatis
     */
    public function test_sales_tax_output_calculation()
    {
        $taxAccount = Account::where('code', '2106')->firstOrFail();

        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TAX-001',
            'journal_date' => '2026-02-15',
            'description' => 'Penjualan Kena Pajak 11%',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                // Line 1: Kas (Total tagihan termasuk PPN)
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 1110000, 'credit' => 0],
                // Line 2: Pendapatan (DPP)
                [
                    'account_uuid' => $this->revenueAccount->uuid,
                    'debit' => 0,
                    'credit' => 1000000,
                    'taxable' => true,
                    'tax_rate' => 11,
                    'tax_type' => 'output'
                ],
            ]
        ];

        $journal = $this->journalService->createJournal(JournalEntity::fromArray($journalData));

        $this->assertNotNull($journal);

        // Refresh journal to get saved lines
        $journal->load('lines');

        // lines 1: Cash
        // lines 2: Revenue
        // lines 3: PPN automatic
        $this->assertCount(3, $journal->lines);

        $taxLine = $journal->lines->firstWhere('account_uuid', $taxAccount->uuid);
        $this->assertNotNull($taxLine, "Baris pajak PPN (2106) harus terbuat otomatis");

        $this->assertEquals(110000, (float)$taxLine->credit, "Jumlah pajak 11% harus Rp 110.000");
    }
}
