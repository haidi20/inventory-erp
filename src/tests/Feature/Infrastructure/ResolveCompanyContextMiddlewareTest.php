<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Http\Middleware\ResolveCompanyContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ResolveCompanyContextMiddlewareTest extends TestCase
{
    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['deleted_at' => 0]);
        $this->companyB = Company::factory()->create(['deleted_at' => 0]);
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);

        $this->user = User::factory()->create();

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_default' => true,
        ]);
        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyB->uuid,
            'is_default' => false,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'is_default' => true,
        ]);
    }

    private function makeRequest(?User $user, array $headers = []): Request
    {
        $request = Request::create('/probe', 'GET');

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        if ($user !== null) {
            $request->setUserResolver(fn () => $user);
        }

        return $request;
    }

    private function handle(Request $request): Request
    {
        $middleware = new ResolveCompanyContext();
        $middleware->handle($request, fn (Request $req) => new Response('ok'));

        return $request;
    }

    private function assertForbidden(Request $request): void
    {
        try {
            $this->handle($request);
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());

            return;
        }

        $this->fail('Expected HttpException 403 was not thrown.');
    }

    public function test_context_defaults_to_pivot_default_without_header(): void
    {
        $request = $this->handle($this->makeRequest($this->user));
        $context = $request->attributes->get('company_context');

        $this->assertSame($this->companyA->uuid, $context->companyUuid);
        $this->assertSame($this->branchA->uuid, $context->branchUuid);
    }

    public function test_header_company_without_membership_is_forbidden(): void
    {
        $companyC = Company::factory()->create(['deleted_at' => 0]);

        $this->assertForbidden(
            $this->makeRequest($this->user, ['X-Klinik-Company-Uuid' => $companyC->uuid])
        );
    }

    public function test_header_to_member_company_switches_context(): void
    {
        $request = $this->handle(
            $this->makeRequest($this->user, ['X-Klinik-Company-Uuid' => $this->companyB->uuid])
        );
        $context = $request->attributes->get('company_context');

        $this->assertSame($this->companyB->uuid, $context->companyUuid);
        $this->assertSame($this->branchB->uuid, $context->branchUuid);
    }

    public function test_branch_header_from_other_company_is_forbidden(): void
    {
        $this->assertForbidden(
            $this->makeRequest($this->user, [
                'X-Klinik-Company-Uuid' => $this->companyA->uuid,
                'X-Klinik-Branch-Uuid' => $this->branchB->uuid,
            ])
        );
    }

    public function test_user_without_pivot_is_forbidden_even_with_header(): void
    {
        $orphanUser = User::factory()->create();

        $this->assertForbidden(
            $this->makeRequest($orphanUser, ['X-Klinik-Company-Uuid' => $this->companyA->uuid])
        );
    }

    public function test_superadmin_can_use_any_company_header(): void
    {
        $superadmin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin->assignRole($role);

        $request = $this->handle(
            $this->makeRequest($superadmin, ['X-Klinik-Company-Uuid' => $this->companyB->uuid])
        );
        $context = $request->attributes->get('company_context');

        $this->assertSame($this->companyB->uuid, $context->companyUuid);
    }

    public function test_changing_default_company_pivot_updates_context(): void
    {
        UserCompany::query()
            ->where('user_uuid', $this->user->uuid)
            ->where('company_uuid', $this->companyA->uuid)
            ->update(['is_default' => false]);

        UserCompany::query()
            ->where('user_uuid', $this->user->uuid)
            ->where('company_uuid', $this->companyB->uuid)
            ->first()
            ->update(['is_default' => true]);

        $request = $this->handle($this->makeRequest($this->user));
        $context = $request->attributes->get('company_context');

        $this->assertSame($this->companyB->uuid, $context->companyUuid);
    }
}
