<?php

namespace Tests\Feature\Repositories\Infrastructure;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Repositories\AppMenuRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppMenuRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AppMenuRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AppMenuRepository();
    }

    public function test_get_sidebar_definitions_returns_config_value()
    {
        $mockMenu = ['home' => ['label' => 'Home', 'url' => '/']];
        Config::set('menus.sidebar_menu', $mockMenu);

        $definitions = $this->repository->getSidebarDefinitions();

        $this->assertEquals($mockMenu, $definitions);
    }

    public function test_get_user_permission_names_returns_sorted_list_of_permissions()
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'report_manager',
            'label' => 'Report Manager',
            'guard_name' => 'web',
        ]);
        $user->assignRole($role);

        $perm1 = Permission::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'view_reports',
            'label' => 'View Reports',
            'guard_name' => 'web',
        ]);
        $perm2 = Permission::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'edit_users',
            'label' => 'Edit Users',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions([$perm1, $perm2]);

        $permissions = $this->repository->getUserPermissionNames($user->uuid);

        $this->assertCount(2, $permissions);
        $this->assertEquals(['edit_users', 'view_reports'], $permissions); // Sorted
    }

    public function test_get_user_permission_names_returns_empty_array_if_user_not_found()
    {
        $permissions = $this->repository->getUserPermissionNames('non-existent-uuid');

        $this->assertIsArray($permissions);
        $this->assertEmpty($permissions);
    }
}
