<?php

namespace Tests\Feature\Controllers\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class GeneralLedgerControllerTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->withoutMiddleware();
    }

    public function test_can_list_general_ledger_for_current_company()
    {
        $account = Account::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => '1001-SBX',
            'name' => 'Kas Sandbox',
            'level' => 1,
            'type' => 'ASSET',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);
        $journal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'transaction_code' => 'TRX-SBX',
            'reference_no' => 'JRN-SBX',
            'journal_date' => time(),
            'status' => 'posted',
        ]);
        $line = JournalLine::query()->create([
            'uuid' => (string) Str::uuid(),
            'journal_uuid' => $journal->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'account_uuid' => $account->uuid,
            'debit' => 1000,
            'credit' => 0,
        ]);

        GeneralLedger::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'account_uuid' => $account->uuid,
            'journal_uuid' => $journal->uuid,
            'journal_line_uuid' => $line->uuid,
            'journal_date' => time(),
            'reference_no' => 'GL-SBX',
            'debit' => 1000,
            'credit' => 0,
            'balance' => 1000,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/general-ledger');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_no', 'GL-SBX');
    }

    public function test_index_only_returns_general_ledger_from_authenticated_users_company()
    {
        $account = Account::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => '1001-SBX',
            'name' => 'Kas Sandbox',
            'level' => 1,
            'type' => 'ASSET',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);
        $journal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'transaction_code' => 'TRX-SBX',
            'reference_no' => 'JRN-SBX',
            'journal_date' => time(),
            'status' => 'posted',
        ]);
        $line = JournalLine::query()->create([
            'uuid' => (string) Str::uuid(),
            'journal_uuid' => $journal->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'account_uuid' => $account->uuid,
            'debit' => 1000,
            'credit' => 0,
        ]);

        GeneralLedger::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'account_uuid' => $account->uuid,
            'journal_uuid' => $journal->uuid,
            'journal_line_uuid' => $line->uuid,
            'journal_date' => time(),
            'reference_no' => 'GL-SBX',
            'debit' => 1000,
            'credit' => 0,
            'balance' => 1000,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherAccount = Account::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'code' => '1001-OTH',
            'name' => 'Kas Other',
            'level' => 1,
            'type' => 'ASSET',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);
        $otherJournal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'transaction_code' => 'TRX-OTH',
            'reference_no' => 'JRN-OTH',
            'journal_date' => time(),
            'status' => 'posted',
        ]);
        $otherLine = JournalLine::query()->create([
            'uuid' => (string) Str::uuid(),
            'journal_uuid' => $otherJournal->uuid,
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'account_uuid' => $otherAccount->uuid,
            'debit' => 1000,
            'credit' => 0,
        ]);

        GeneralLedger::query()->create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'account_uuid' => $otherAccount->uuid,
            'journal_uuid' => $otherJournal->uuid,
            'journal_line_uuid' => $otherLine->uuid,
            'journal_date' => time(),
            'reference_no' => 'GL-OTH',
            'debit' => 1000,
            'credit' => 0,
            'balance' => 1000,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/general-ledger');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_no', 'GL-SBX');
    }
}
