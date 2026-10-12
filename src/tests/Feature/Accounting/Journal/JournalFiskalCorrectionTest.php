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
use App\Modules\Core\Authorization\Models\Role;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalFiskalCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JournalService::class);
        $this->branch = Branch::create(['name' => 'Test Branch', 'short_name' => 'TST', 'is_active' => true]);

        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $role = Role::firstOrCreate(['name' => 'Tax Officer'], ['is_active' => true]);
        $user->roles()->syncWithoutDetaching([$role->uuid]);
        $this->actingAs($user);
        
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
     * Skenario 37.1: Jurnal dengan Koreksi Fiskal Positif (Beban Non-Deductible)
     */
    public function test_journal_with_fiscal_correction(): void
    {
        $accBebanNon = Account::create(['code' => '5999', 'name' => 'Beban Jamuan', 'type' => AccountType::EXPENSE, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::INCOME_STATEMENT]);
        $accKas = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => AccountType::ASSET, 'type_saldo' => 'debit', 'financial_statement' => FinancialStatementType::BALANCE_SHEET]);

        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => now()->toDateString(),
            'reference_no' => 'FISCAL-001',
            'reference_type' => JournalReferenceType::EXPENSE,
            'lines' => [
                [
                    'account_uuid' => $accBebanNon->uuid,
                    'debit' => 250000,
                    'credit' => 0,
                    'is_fiscal' => true,
                    'fiscal_type' => 'positive', // Koreksi Fiskal Positif
                ],
                [
                    'account_uuid' => $accKas->uuid,
                    'debit' => 0,
                    'credit' => 250000,
                    'is_fiscal' => false,
                ],
            ],
        ]);

        $journal = $this->service->createJournal($dto);

        $line = $journal->lines->where('account_uuid', $accBebanNon->uuid)->first();
        $this->assertTrue((bool)$line->is_fiscal);
        $this->assertEquals('positive', $line->fiscal_type);
    }
}
