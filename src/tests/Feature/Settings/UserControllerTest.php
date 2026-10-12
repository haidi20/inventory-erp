<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Inertia\Inertia::setRootView('layouts.app');

        // Ensure Spatie uses the array cache for isolation
        config(['permission.cache.store' => 'array']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed basic permissions
        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.user.view', 'guard_name' => 'web', 'label' => 'View Users']);
        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.user.manage', 'guard_name' => 'web', 'label' => 'Manage Users']);
        
        $role = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin']);
        $role->givePermissionTo(['settings.user.view', 'settings.user.manage']);
    }

    /** @test */
    public function it_can_render_user_index_page_via_inertia()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('settings.users.index'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Core/Identity/presentations/views/UserIndex', false)
                ->has('users.data')
                ->has('filters')
            );
    }

    /** @test */
    public function it_can_filter_users_by_search_query()
    {
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('admin');
        
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $this->actingAs($admin)
            ->get(route('settings.users.index', ['search' => 'John']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'John Doe')
            );
    }

    /** @test */
    public function it_rejects_unauthorized_access()
    {
        $user = User::factory()->create(); // No role

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->get(route('settings.users.index'))
            ->assertForbidden();
    }

    /** @test */
    public function it_can_store_new_user_with_valid_data()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'name' => 'New Staff',
            'email' => 'staff@klinik.local',
            'password' => 'password123',
            'role_uuids' => [Role::where('name', 'admin')->first()->uuid],
        ];

        $this->actingAs($admin)
            ->post(route('settings.users.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'staff@klinik.local']);
    }

    /** @test */
    public function it_validates_required_fields_when_storing()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('settings.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role_ids']);
    }
}
