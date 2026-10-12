<?php

namespace Tests\Feature\Controllers\Accounting;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    private User $user;

    private Branch $branch;

    private Company $company;

    /** @var list<string> */
    private array $testAccountUuids = [];

    /** @var list<string> */
    private array $testBranchUuids = [];

    /** @var list<string> */
    private array $testCompanyUuids = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create();

        $this->testCompanyUuids[] = $this->company->uuid;
        $this->testBranchUuids[] = $this->branch->uuid;

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);

        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        Account::withTrashed()
            ->whereIn('uuid', $this->testAccountUuids)
            ->forceDelete();
        UserBranch::where('user_uuid', $this->user->uuid)->forceDelete();
        UserCompany::where('user_uuid', $this->user->uuid)->forceDelete();
        User::where('uuid', $this->user->uuid)->forceDelete();
        Branch::whereIn('uuid', $this->testBranchUuids)->forceDelete();
        Company::whereIn('uuid', $this->testCompanyUuids)->forceDelete();

        parent::tearDown();
    }

    private function makeAccount(array $overrides = []): Account
    {
        $account = Account::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => '1001-'.Str::upper(Str::random(4)),
            'name' => 'Kas',
            'level' => 1,
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ], $overrides));

        $this->testAccountUuids[] = $account->uuid;

        return $account;
    }

    private function makeOtherCompanyAccount(array $overrides = []): Account
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $this->testCompanyUuids[] = $otherCompany->uuid;
        $this->testBranchUuids[] = $otherBranch->uuid;

        $account = Account::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'code' => '1001-'.Str::upper(Str::random(4)),
            'name' => 'Kas Other',
            'level' => 1,
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ], $overrides));

        $this->testAccountUuids[] = $account->uuid;

        return $account;
    }

    public function test_can_list_accounts_for_current_company()
    {
        $this->makeAccount(['code' => '1001-SBX']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/accounts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '1001-SBX');
    }

    public function test_index_only_returns_accounts_from_authenticated_users_company()
    {
        $this->makeAccount(['code' => '1001-SBX']);
        $this->makeOtherCompanyAccount(['code' => '1001-OTH', 'name' => 'Kas Other']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/accounts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '1001-SBX');
    }

    public function test_search_is_performed_on_the_database_server()
    {
        $this->makeAccount(['code' => '1001-SBX', 'name' => 'Kas Utama']);
        $this->makeAccount(['code' => '5001-SBX', 'name' => 'Pendapatan']);
        $this->makeOtherCompanyAccount(['code' => '1001-OTH', 'name' => 'Kas Other']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/accounts?search=Kas&search_column=name');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '1001-SBX')
            ->assertJsonPath('data.0.name', 'Kas Utama');
    }

    public function test_pagination_respects_server_side_page_and_per_page()
    {
        foreach (range(1, 15) as $index) {
            $this->makeAccount([
                'code' => sprintf('90%02d', $index),
                'name' => 'Akun Pagination '.$index,
            ]);
        }

        $response = $this->actingAs($this->user)->getJson('/api/v1/accounts?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.code', '9011');
    }

    public function test_can_create_account_in_current_company()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/accounts', [
            'code' => '2001-SBX',
            'name' => 'Hutang Usaha',
            'type' => 'liability',
            'is_active' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('accounts', [
            'code' => '2001-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->testAccountUuids[] = $response->json('data.uuid');
    }

    public function test_can_show_account_from_current_company()
    {
        $account = $this->makeAccount(['code' => '1002-SBX']);

        $response = $this->actingAs($this->user)->getJson("/api/v1/accounts/{$account->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $account->uuid);
    }

    public function test_cannot_show_account_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $this->testCompanyUuids[] = $otherCompany->uuid;
        $this->testBranchUuids[] = $otherBranch->uuid;

        $account = Account::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'code' => '1002-OTH',
            'name' => 'Kas Other',
            'level' => 1,
            'type' => 'asset',
            'type_saldo' => 'debit',
            'financial_statement' => 'balance_sheet',
            'is_header' => false,
            'is_active' => true,
        ]);

        $this->testAccountUuids[] = $account->uuid;

        $response = $this->actingAs($this->user)->getJson("/api/v1/accounts/{$account->uuid}");

        $response->assertNotFound();
    }

    public function test_can_update_account_from_current_company()
    {
        $account = $this->makeAccount(['code' => '3001-SBX', 'name' => 'Modal Lama']);

        $response = $this->actingAs($this->user)->putJson("/api/v1/accounts/{$account->uuid}", [
            'name' => 'Modal Baru',
        ]);

        $response->assertOk();
        $this->assertSame('Modal Baru', $account->fresh()->name);
    }

    public function test_can_delete_account_in_current_context()
    {
        $account = $this->makeAccount(['code' => '4001-SBX']);

        $response = $this->actingAs($this->user)->deleteJson('/api/v1/accounts/'.$account->uuid);

        $response->assertNoContent();
        $this->assertSoftDeleted('accounts', ['uuid' => $account->uuid]);
    }

    public function test_cannot_delete_account_from_another_company_or_branch()
    {
        $account = $this->makeOtherCompanyAccount(['code' => '4001-OTH']);

        $response = $this->actingAs($this->user)->deleteJson('/api/v1/accounts/'.$account->uuid);

        $response->assertNotFound();
        $this->assertDatabaseHas('accounts', [
            'uuid' => $account->uuid,
            'deleted_at' => 0,
        ]);
    }
}
