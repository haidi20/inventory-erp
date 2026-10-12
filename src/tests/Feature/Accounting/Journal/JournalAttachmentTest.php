<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Models\JournalAttachment;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalAttachmentTest extends TestCase
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
        
        $user = User::first();
        $this->actingAs($user);
        
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
     * Skenario 36.1: Upload Attachment ke Jurnal
     */
    public function test_journal_attachment_creation(): void
    {
        $accKas = Account::updateOrCreate(['code' => '1101'], ['name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::BALANCE_SHEET]);
        $accRev = Account::updateOrCreate(['code' => '4101'], ['name' => 'Pendapatan', 'type' => AccountType::REVENUE, 'type_saldo' => 'credit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'ATTACH-001',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accKas->uuid, 'debit' => 500, 'credit' => 0],
                ['account_uuid' => $accRev->uuid, 'debit' => 0, 'credit' => 500],
            ],
        ]);

        $journal = $this->service->createJournal($dto);

        $attachment = JournalAttachment::create([
            'journal_uuid' => $journal->uuid,
            'file_name' => 'invoice.pdf',
            'file_path' => 'attachments/invoice.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $this->assertDatabaseHas('journal_attachments', [
            'journal_uuid' => $journal->uuid,
            'file_name' => 'invoice.pdf',
        ]);
        
        $this->assertEquals(1, $journal->fresh()->attachments()->count());
    }
}

