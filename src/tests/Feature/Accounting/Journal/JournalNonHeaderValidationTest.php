<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalNonHeaderValidationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->branch = Branch::create([
            'name' => 'Validation Branch',
            'short_name' => 'VAL',
            'is_active' => true,
        ]);

        User::factory()->create(['branch_uuid' => $this->branch->uuid]);

        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Current Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);
    }

    public function test_cannot_post_to_header_account(): void
    {
        $headerAccount = Account::create([
            'code' => '1000',
            'name' => 'Aset Header',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => true,
            'is_active' => true,
        ]);

        $detailAccount = Account::create([
            'code' => '1101',
            'name' => 'Kas',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'HDR-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Header account should fail',
            'lines' => [
                ['account_uuid' => $headerAccount->uuid, 'debit' => 100000, 'credit' => 0],
                ['account_uuid' => $detailAccount->uuid, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot post to header account');

        $this->journalService->createJournal($dto);
    }

    public function test_cannot_post_to_inactive_account(): void
    {
        $inactiveAccount = Account::create([
            'code' => '1102',
            'name' => 'Bank Nonaktif',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => false,
        ]);

        $detailAccount = Account::create([
            'code' => '4101',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'type_saldo' => 'credit',
            'financial_statement' => 'income_statement',
            'is_header' => false,
            'is_active' => true,
        ]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'INA-' . uniqid(),
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Inactive account should fail',
            'lines' => [
                ['account_uuid' => $inactiveAccount->uuid, 'debit' => 100000, 'credit' => 0],
                ['account_uuid' => $detailAccount->uuid, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Account is inactive');

        $this->journalService->createJournal($dto);
    }
}
