<?php

namespace Tests\Feature\Api;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiRoutePermissionTest extends TestCase
{
    private User $userWithoutPermission;
    private User $userWithMasterPermission;
    private User $userWithLookupPermission;
    private User $userWithOperationalPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create();
        $branch = Branch::factory()->create([
            'company_uuid' => $company->uuid,
        ]);

        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'master.manage',
            'guard_name' => 'web',
            'label' => 'Manage Master API',
        ]);

        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'master.lookup.view',
            'guard_name' => 'web',
            'label' => 'View Lookup API',
        ]);

        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'operational.manage',
            'guard_name' => 'web',
            'label' => 'Manage Operational API',
        ]);

        $masterRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'api-master',
            'guard_name' => 'web',
            'label' => 'API Master',
        ]);
        $masterRole->givePermissionTo('master.manage');

        $lookupRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'api-lookup',
            'guard_name' => 'web',
            'label' => 'API Lookup',
        ]);
        $lookupRole->givePermissionTo('master.lookup.view');

        $operationalRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'api-operational',
            'guard_name' => 'web',
            'label' => 'API Operational',
        ]);
        $operationalRole->givePermissionTo('operational.manage');

        $this->userWithoutPermission = User::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);

        $this->userWithMasterPermission = User::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);
        $this->userWithMasterPermission->assignRole($masterRole);

        $this->userWithLookupPermission = User::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);
        $this->userWithLookupPermission->assignRole($lookupRole);

        $this->userWithOperationalPermission = User::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);
        $this->userWithOperationalPermission->assignRole($operationalRole);
    }

    public function test_products_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/products')->assertUnauthorized();
    }

    public function test_products_route_forbids_user_without_master_permission(): void
    {
        $this->actingAs($this->userWithoutPermission, 'api')
            ->getJson('/api/v1/products')
            ->assertForbidden();
    }

    public function test_products_route_allows_user_with_master_permission(): void
    {
        $this->actingAs($this->userWithMasterPermission, 'api')
            ->getJson('/api/v1/products')
            ->assertOk();
    }

    public function test_lookup_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/lov/sets')->assertUnauthorized();
    }

    public function test_lookup_route_forbids_user_without_lookup_permission(): void
    {
        $this->actingAs($this->userWithoutPermission, 'api')
            ->getJson('/api/v1/lov/sets')
            ->assertForbidden();
    }

    public function test_lookup_route_allows_user_with_lookup_permission(): void
    {
        $this->actingAs($this->userWithLookupPermission, 'api')
            ->getJson('/api/v1/lov/sets')
            ->assertOk();
    }

    public function test_sales_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/sales')->assertUnauthorized();
    }

    public function test_sales_route_forbids_user_without_operational_permission(): void
    {
        $this->actingAs($this->userWithoutPermission, 'api')
            ->getJson('/api/v1/sales')
            ->assertForbidden();
    }

    public function test_sales_route_allows_user_with_operational_permission(): void
    {
        $this->actingAs($this->userWithOperationalPermission, 'api')
            ->getJson('/api/v1/sales')
            ->assertOk();
    }
}
