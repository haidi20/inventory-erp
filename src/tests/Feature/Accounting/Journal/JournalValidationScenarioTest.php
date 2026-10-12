<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;
use Exception;

class JournalValidationScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'short_name' => 'TST',
            'is_active' => true,
        ]);
        
        // Setup initial accounting period
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => now()->format('Y-m'),
            'name' => 'Period Test',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);
    }

    /**
     * Skenario 23.1: Posting ke Akun Header (Gagal)
     */
    public function test_cannot_post_to_header_account(): void
    {
        // 1. Setup Header Account
        $headerAccount = Account::create([
            'is_header' => true,
            'is_active' => true,
            'code' => '1000',
            'name' => 'ASET',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        $normalAccount = Account::create([
            'is_header' => false,
            'is_active' => true,
            'code' => '1101',
            'name' => 'Kas',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        // 2. Prepare Journal Data
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-' . uniqid(),
            'reference_type' => JournalReferenceType::REVERSAL,
            'description' => 'Test Header',
            'lines' => [
                [
                    'account_uuid' => $headerAccount->uuid,
                    'debit' => 1000,
                    'credit' => 0,
                ],
                [
                    'account_uuid' => $normalAccount->uuid,
                    'debit' => 0,
                    'credit' => 1000,
                ],
            ],
        ]);

        // 3. Execution & Assertion
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot post to header account');

        $this->service->createJournal($dto);
    }

    /**
     * Skenario 23.2: Posting ke Akun Tidak Aktif (Gagal)
     */
    public function test_cannot_post_to_inactive_account(): void
    {
        // 1. Setup Inactive Account
        $inactiveAccount = Account::create([
            'is_header' => false,
            'is_active' => false,
            'code' => '1102',
            'name' => 'Bank Inaktif',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        $normalAccount = Account::create([
            'is_header' => false,
            'is_active' => true,
            'code' => '1101',
            'name' => 'Kas',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        // 2. Prepare Journal Data
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-' . uniqid(),
            'reference_type' => JournalReferenceType::REVERSAL,
            'description' => 'Test Inactive',
            'lines' => [
                [
                    'account_uuid' => $inactiveAccount->uuid,
                    'debit' => 1000,
                    'credit' => 0,
                ],
                [
                    'account_uuid' => $normalAccount->uuid,
                    'debit' => 0,
                    'credit' => 1000,
                ],
            ],
        ]);

        // 3. Execution & Assertion
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Account is inactive');

        $this->service->createJournal($dto);
    }

    /**
     * Skenario 23.3: Posting ke Akun Berhasil
     */
    public function test_can_post_to_active_non_header_account(): void
    {
        // 1. Setup Valid Accounts
        $account1 = Account::create([
            'is_header' => false,
            'is_active' => true,
            'code' => 'ACC01',
            'name' => 'Account 1',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        $account2 = Account::create([
            'is_header' => false,
            'is_active' => true,
            'code' => 'ACC02',
            'name' => 'Account 2',
            'type' => AccountType::ASSET,
            'type_saldo' => 'debit',
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
        ]);

        // 2. Prepare Journal Data
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'REF-' . uniqid(),
            'reference_type' => JournalReferenceType::REVERSAL,
            'description' => 'Test Valid',
            'lines' => [
                [
                    'account_uuid' => $account1->uuid,
                    'debit' => 1000,
                    'credit' => 0,
                ],
                [
                    'account_uuid' => $account2->uuid,
                    'debit' => 0,
                    'credit' => 1000,
                ],
            ],
        ]);

        // 3. Execution
        $journal = $this->service->createJournal($dto);

        // 4. Assertion
        $this->assertNotNull($journal);
        $this->assertEquals(2, $journal->lines->count());
    }
}
