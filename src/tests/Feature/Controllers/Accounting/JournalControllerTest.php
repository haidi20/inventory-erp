<?php

namespace Tests\Feature\Controllers\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class JournalControllerTest extends TestCase
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

    public function test_can_list_journals_for_current_company()
    {
        $journal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'transaction_code' => 'TRX-SBX',
            'reference_no' => 'JRN-SBX',
            'journal_date' => time(),
            'status' => 'draft',
        ]);

        $account = Account::query()->create([
            'uuid' => (string) Str::uuid(),
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

        JournalLine::query()->create([
            'uuid' => (string) Str::uuid(),
            'journal_uuid' => $journal->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'account_uuid' => $account->uuid,
            'debit' => 1000,
            'credit' => 0,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/journals');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_no', 'JRN-SBX');
    }

    public function test_index_only_returns_journals_from_authenticated_users_company()
    {
        Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'transaction_code' => 'TRX-SBX',
            'reference_no' => 'JRN-SBX',
            'journal_date' => time(),
            'status' => 'draft',
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'transaction_code' => 'TRX-OTH',
            'reference_no' => 'JRN-OTH',
            'journal_date' => time(),
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/journals');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_no', 'JRN-SBX');
    }

    public function test_can_show_journal_from_current_company()
    {
        $journal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'transaction_code' => 'TRX-SHOW',
            'reference_no' => 'JRN-SHOW',
            'journal_date' => time(),
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/journals/{$journal->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $journal->uuid);
    }

    public function test_cannot_show_journal_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $journal = Journal::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'transaction_code' => 'TRX-OTH',
            'reference_no' => 'JRN-OTH',
            'journal_date' => time(),
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/journals/{$journal->uuid}");

        $response->assertNotFound();
    }
}
