<?php

namespace Tests\Feature\Api;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPermissionE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_non_superadmin_without_permission_is_denied(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web', 'label' => 'Staff']);
        $user->assignRole($role);
        UserCompany::create(['user_uuid' => $user->uuid, 'company_uuid' => $company->uuid, 'is_default' => true]);
        UserBranch::create(['user_uuid' => $user->uuid, 'company_uuid' => $company->uuid, 'branch_uuid' => $branch->uuid, 'is_default' => true]);

        $this->actingAs($user, 'api')->getJson('/api/v1/products')->assertStatus(403);
        $this->actingAs($user, 'api')->getJson('/api/v1/products/export')->assertStatus(403);
        $this->actingAs($user, 'api')->getJson('/api/v1/products/import/template')->assertStatus(403);
    }
}
