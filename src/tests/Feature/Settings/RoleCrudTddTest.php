<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleCrudTddTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        config(['permission.cache.store' => 'array']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.role.view', 'guard_name' => 'web', 'label' => 'View Roles']);
        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.role.manage', 'guard_name' => 'web', 'label' => 'Manage Roles']);
        
        $this->adminRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin']);
        $this->adminRole->givePermissionTo(['settings.role.view', 'settings.role.manage']);

        $this->p1 = Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'test.p1', 'guard_name' => 'web', 'label' => 'P1']);
        $this->p2 = Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'test.p2', 'guard_name' => 'web', 'label' => 'P2']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    /** @test */
    public function it_can_create_a_new_role_with_permissions()
    {
        $roleData = [
            'name' => 'manager',
            'label' => 'Manager',
            'permissions' => [$this->p1->uuid, $this->p2->uuid]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/settings/roles', $roleData);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'manager', 'label' => 'Manager']);
        
        $role = Role::where('name', 'manager')->first();
        $this->assertTrue($role->hasPermissionTo('test.p1'));
        $this->assertTrue($role->hasPermissionTo('test.p2'));
    }

    /** @test */
    public function it_can_update_a_role()
    {
        $role = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'editor', 'label' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo('test.p1');

        $updateData = [
            'name' => 'editor_updated',
            'label' => 'Super Editor',
            'permissions' => [$this->p2->uuid] // Replace p1 with p2
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/settings/roles/{$role->uuid}", $updateData);

        $response->assertRedirect();
        
        $role->refresh();
        $this->assertEquals('editor_updated', $role->name);
        $this->assertEquals('Super Editor', $role->label);
        $this->assertFalse($role->hasPermissionTo('test.p1'));
        $this->assertTrue($role->hasPermissionTo('test.p2'));
    }

    /** @test */
    public function it_can_delete_a_role()
    {
        $role = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'to_delete', 'label' => 'Delete Me', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)
            ->delete("/settings/roles/{$role->uuid}");

        $response->assertRedirect();
        $this->assertSoftDeleted('roles', ['uuid' => $role->uuid]);
    }
}
