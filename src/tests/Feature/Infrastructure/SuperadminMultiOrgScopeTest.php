<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuperadminMultiOrgScopeTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private Branch $branchA;

    private Branch $branchB;

    private User $superadmin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['deleted_at' => 0]);
        $this->companyB = Company::factory()->create(['deleted_at' => 0]);
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);

        $this->createAccount('ACC-A', $this->companyA->uuid, $this->branchA->uuid);
        $this->createAccount('ACC-B', $this->companyB->uuid, $this->branchB->uuid);

        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole($role);

        $this->user = User::factory()->create();
        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'is_default' => true,
        ]);
    }

    public function test_superadmin_sees_all_companies_and_branches_without_scope_filters(): void
    {
        $this->actingAsSuperadmin();
        $this->useQuery([]);

        $accounts = Account::query()->get();

        $this->assertSame(2, $accounts->count());
        $companyUuids = $accounts->pluck('company_uuid')->all();
        sort($companyUuids);

        $expectedCompanyUuids = [$this->companyA->uuid, $this->companyB->uuid];
        sort($expectedCompanyUuids);

        $this->assertSame($expectedCompanyUuids, $companyUuids);
    }

    public function test_superadmin_can_filter_records_by_company_query(): void
    {
        $this->actingAsSuperadmin();
        $this->useQuery(['company_uuid' => $this->companyB->uuid]);

        $accounts = Account::query()->get();

        $this->assertSame(1, $accounts->count());
        $this->assertSame($this->companyB->uuid, $accounts->first()->company_uuid);
    }

    public function test_superadmin_can_filter_records_by_branch_query(): void
    {
        $this->actingAsSuperadmin();
        $this->useQuery(['branch_uuid' => $this->branchB->uuid]);

        $accounts = Account::query()->get();

        $this->assertSame(1, $accounts->count());
        $this->assertSame($this->companyB->uuid, $accounts->first()->company_uuid);
        $this->assertSame($this->branchB->uuid, $accounts->first()->branch_uuid);
    }

    public function test_regular_user_cannot_use_query_filters_to_escape_scope(): void
    {
        $this->actingAs($this->user);
        Auth::shouldUse('web');
        $this->useQuery([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $accounts = Account::query()->get();

        $this->assertSame(1, $accounts->count());
        $this->assertSame($this->companyA->uuid, $accounts->first()->company_uuid);
        $this->assertSame($this->branchA->uuid, $accounts->first()->branch_uuid);
    }

    private function actingAsSuperadmin(): void
    {
        $this->actingAs($this->superadmin);
        Auth::shouldUse('web');
    }

    private function useQuery(array $query): void
    {
        $this->app->instance('request', Request::create('/', 'GET', $query));
    }

    private function createAccount(string $code, string $companyUuid, string $branchUuid): void
    {
        Account::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => $code,
            'name' => 'Kas Test ' . $code,
            'type' => 'asset',
        ]);
    }
}
