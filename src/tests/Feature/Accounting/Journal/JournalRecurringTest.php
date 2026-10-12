<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\RecurringJournal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalRecurringTest extends TestCase
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
            ClosingPeriod::updateOrCreate(
                ['branch_uuid' => $this->branch->uuid, 'period' => now()->format('Y-m')],
                [
                    'name' => 'Period Test',
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'open',
                ]
            );
        });
    }

    /**
     * Skenario 32.1: Recurring Bulanan (Sewa Dibayar Dimuka)
     */
    public function test_automatic_recurring_journal_creation(): void
    {
        $accSewa = Account::where('code', '5101')->firstOrFail();
        $accKas = Account::where('code', '1101')->firstOrFail();

        $journalData = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'REC-TEMPLATE-001',
            'reference_type' => JournalReferenceType::GENERAL->value,
            'description' => 'Biaya Sewa Bulanan',
            'lines' => [
                ['account_uuid' => $accSewa->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $accKas->uuid, 'debit' => 0, 'credit' => 1000000],
            ]
        ];

        $template = RecurringJournal::create([
            'branch_uuid' => $this->branch->uuid,
            'template_name' => 'Sewa Kantor',
            'frequency' => 'monthly',
            'next_run_date' => now()->toDateString(),
            'journal_data' => $journalData,
            'status' => 'active',
        ]);

        $count = $this->service->processRecurringJournals();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('journals', [
            'branch_uuid' => $this->branch->uuid,
            'description' => '[RECURRING] Biaya Sewa Bulanan',
        ]);

        $template->refresh();
        $this->assertEquals(now()->addMonth()->toDateString(), $template->next_run_date->toDateString());
    }
}

