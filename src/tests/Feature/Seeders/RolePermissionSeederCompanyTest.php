<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\Supplier;
use Database\Seeders\V1\RolePermissionSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class RolePermissionSeederCompanyTest extends TestCase
{
    public function test_it_uses_the_sandbox_company_uuid_for_the_demo_branch(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $demoBranch = Branch::query()->where('short_name', 'KDM')->first();
        $sandboxBranch = Branch::query()->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();

        $this->assertNotNull($demoBranch);
        $this->assertNotNull($sandboxBranch);
        $this->assertSame($sandboxBranch->company_uuid, $demoBranch->company_uuid);
    }

    public function test_it_scopes_seeded_employees_and_suppliers_to_the_demo_branch_company(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $demoBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $this->assertDatabaseHas('employees', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'role' => 'dokter',
        ]);

        $this->assertDatabaseHas('employees', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'role' => 'kasir',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'name' => 'PT. Kimia Farma',
        ]);

        $this->assertSame(
            $demoBranch->company_uuid,
            Employee::query()->where('branch_uuid', $demoBranch->uuid)->where('role', 'kasir')->firstOrFail()->company_uuid
        );

        $this->assertSame(
            $demoBranch->company_uuid,
            Supplier::query()->where('branch_uuid', $demoBranch->uuid)->where('name', 'PT. Kimia Farma')->firstOrFail()->company_uuid
        );
    }
}
