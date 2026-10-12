<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RestoreFromBackupTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected GeneralLedgerService $generalLedgerService;
    protected Branch $branch;
    protected ClosingPeriod $period;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->generalLedgerService = app(GeneralLedgerService::class);

        $this->branch = Branch::create([
            'name' => 'Backup Branch',
            'short_name' => 'BKP',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);

        $this->period = ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Current Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);

        $this->cashAccount = Account::create([
            'code' => '1101',
            'name' => 'Kas',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);

        $this->revenueAccount = Account::create([
            'code' => '4101',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'financial_statement' => 'income_statement',
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    public function test_can_restore_journal_and_rebuild_ledger_from_backup_snapshot(): void
    {
        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'BACKUP-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Backup snapshot source',
            'lines' => [
                ['account_uuid' => $this->cashAccount->uuid, 'debit' => 900000, 'credit' => 0],
                ['account_uuid' => $this->revenueAccount->uuid, 'debit' => 0, 'credit' => 900000],
            ],
        ]));

        $journalModel = $journal->fresh(['lines']);
        $this->assertNotNull($journalModel);

        $journalSnapshot = [
            'branch_uuid' => $journalModel->branch_uuid,
            'transaction_code' => $journalModel->transaction_code,
            'reference_no' => $journalModel->reference_no,
            'reference_model' => $journalModel->reference_model?->value ?? $journalModel->reference_model,
            'reference_type' => $journalModel->reference_type?->value ?? $journalModel->reference_type,
            'reference_uuid' => $journalModel->reference_uuid,
            'closing_period_uuid' => $journalModel->closing_period_uuid,
            'journal_date' => $journalModel->journal_date,
            'scheduled_at' => $journalModel->scheduled_at,
            'description' => $journalModel->description,
            'status' => $journalModel->status?->value ?? $journalModel->status,
            'reversal_status' => $journalModel->reversal_status,
            'reversal_date' => $journalModel->reversal_date,
            'reversal_journal_uuid' => $journalModel->reversal_journal_uuid,
            'workflow_status' => $journalModel->workflow_status?->value ?? $journalModel->workflow_status,
            'version' => $journalModel->version,
            'is_system' => $journalModel->is_system,
            'is_adjusting' => $journalModel->is_adjusting,
            'is_processed' => $journalModel->is_processed,
            'created_by' => $journalModel->created_by,
            'updated_by' => $journalModel->updated_by,
        ];

        $lineSnapshots = $journalModel->lines->map(function (JournalLine $line) {
            return Arr::only($line->getAttributes(), [
                'branch_uuid',
                'account_uuid',
                'debit',
                'credit',
                'taxable',
                'tax_rate',
                'tax_type',
                'foreign_currency',
                'foreign_amount',
                'exchange_rate',
                'is_processed',
                'contact_uuid',
                'created_by',
                'updated_by',
            ]);
        })->all();

        DB::table('general_ledgers')->delete();
        DB::table('journal_lines')->delete();
        DB::table('journals')->delete();

        $this->assertSame(0, Journal::count());
        $this->assertSame(0, JournalLine::count());

        $restoredJournal = Journal::create($journalSnapshot);

        foreach ($lineSnapshots as $lineSnapshot) {
            $restoredJournal->lines()->create($lineSnapshot);
        }

        $restoredJournal->load('lines');

        $this->assertSame('BACKUP-001', $restoredJournal->reference_no);
        $this->assertCount(2, $restoredJournal->lines);
        $this->assertEquals(900000.0, (float) $restoredJournal->lines->sum('debit'));
        $this->assertEquals(900000.0, (float) $restoredJournal->lines->sum('credit'));

        $this->generalLedgerService->syncByPeriod($this->period->uuid);

        $this->assertSame(2, GeneralLedger::count());
        $this->assertDatabaseHas('general_ledgers', [
            'reference_no' => 'BACKUP-001',
            'account_uuid' => $this->cashAccount->uuid,
            'debit' => 900000,
        ]);
    }
}
