<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
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

class JournalAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        $this->service = app(JournalService::class);
        $this->branch = Branch::where('short_name', 'KDM')->first();

        $user = User::first();
        $this->actingAs($user);
        $this->user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        Auth::login($this->user);
        
        ClosingPeriod::withoutEvents(function() {
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
     * Skenario 34.1: Pencatatan Audit Saat Create Journal
     */
    public function test_audit_log_captured_on_create(): void
    {
        $accKas = Account::updateOrCreate(['code' => '1101'], ['name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::BALANCE_SHEET]);
        $accRev = Account::updateOrCreate(['code' => '4101'], ['name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'AUDIT-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $journal = $this->service->createJournal($dto);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'entity_type' => Journal::class,
            'entity_uuid' => $journal->uuid,
            'user_uuid' => $this->user->uuid,
        ]);

        $log = AuditLog::where('entity_uuid', $journal->uuid)->first();
        $this->assertNotNull($log->after);
        $this->assertEquals('AUDIT-001', $log->after['reference_no']);
    }
}

