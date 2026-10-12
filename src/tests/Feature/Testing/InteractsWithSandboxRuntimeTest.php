<?php

namespace Tests\Feature\Testing;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\RolePermissionSeeder;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Tests\TestCase;

class InteractsWithSandboxRuntimeTest extends TestCase
{
    use InteractsWithSandboxRuntime;

    public function test_it_provides_reusable_sandbox_runtime_fixtures(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $sandboxCompany = $this->sandboxCompany();
        $sandboxBranch = $this->sandboxBranch();
        $sandboxOwner = $this->sandboxOwner();

        $this->assertInstanceOf(Company::class, $sandboxCompany);
        $this->assertSame($sandboxBranch->company_uuid, $sandboxCompany->uuid);

        $this->assertInstanceOf(Branch::class, $sandboxBranch);
        $this->assertSame($sandboxCompany->uuid, $sandboxBranch->company_uuid);

        $this->assertInstanceOf(User::class, $sandboxOwner);
        $this->assertSame($sandboxBranch->uuid, $sandboxOwner->branch_uuid);
        $this->assertTrue($sandboxOwner->hasRole('owner'));
    }

    public function test_it_provides_demo_context_for_superadmin_crud(): void
    {
        $superadmin = $this->actingAsDemoSuperadmin();
        $demoCompany = $this->demoCompany();
        $demoBranch = $this->demoBranch();

        $this->assertTrue($superadmin->hasRole('superadmin'));
        $this->assertSame($demoCompany->uuid, $superadmin->active_company_uuid);
        $this->assertSame($demoBranch->uuid, $superadmin->active_branch_uuid);
        $this->assertSame($demoBranch->company_uuid, $demoCompany->uuid);
    }
}
