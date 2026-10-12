<?php

namespace Tests\Unit\Support;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Shared\Support\CompanyScopeResolver;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class CompanyScopeResolverTest extends TestCase
{
    public function test_it_prefers_sandbox_company_uuid_from_header_in_sandbox_environment(): void
    {
        $sandboxCompany = Company::factory()->create();
        Company::factory()->create();

        config(['klinik.runtime_environment' => 'sandbox']);

        $request = request()->duplicate(server: [
            'HTTP_X_KLINIK_ENVIRONMENT' => 'sandbox',
            'HTTP_X_KLINIK_COMPANY_UUID' => $sandboxCompany->uuid,
        ]);

        $resolver = new CompanyScopeResolver();

        $this->assertSame($sandboxCompany->uuid, $resolver->resolveCompanyUuid($request));
    }

    public function test_it_prefers_authenticated_user_company_uuid_in_production(): void
    {
        $userCompany = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $userCompany->uuid]);
        $user = User::factory()->create();

        UserCompany::create([
            'user_uuid' => $user->uuid,
            'company_uuid' => $userCompany->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $user->uuid,
            'company_uuid' => $userCompany->uuid,
            'branch_uuid' => $branch->uuid,
            'is_default' => true,
        ]);

        config(['klinik.runtime_environment' => 'production']);

        $request = request()->duplicate();
        $request->setUserResolver(fn () => $user->fresh()->load('branches'));

        $resolver = new CompanyScopeResolver();

        $this->assertSame($userCompany->uuid, $resolver->resolveCompanyUuid($request));
    }

    public function test_current_context_helpers_fall_back_to_authenticated_user(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $user = User::factory()->create();

        UserCompany::create([
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid' => $branch->uuid,
            'is_default' => true,
        ]);

        request()->setUserResolver(fn () => $user);

        $this->assertSame($company->uuid, current_company_uuid());
        $this->assertSame($branch->uuid, current_branch_uuid());
    }

    public function test_it_uses_seeded_sandbox_branch_company_when_no_header_is_provided_in_sandbox(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        config(['klinik.runtime_environment' => 'sandbox']);

        $sandboxBranch = Branch::query()->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->firstOrFail();
        $request = request()->duplicate(server: [
            'HTTP_X_KLINIK_ENVIRONMENT' => 'sandbox',
        ]);

        $resolver = new CompanyScopeResolver();

        $this->assertSame($sandboxBranch->company_uuid, $resolver->resolveCompanyUuid($request));
    }
}
