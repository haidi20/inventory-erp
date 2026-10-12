<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class UserServerSideFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Inertia\Inertia::setRootView('layouts.app');
        config(['inertia.testing.ensure_pages_exist' => false]);
        config(['permission.cache.store' => 'array']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.user.view', 'guard_name' => 'web', 'label' => 'View Users']);
        Permission::create(['uuid' => (string) Str::uuid(), 'name' => 'settings.user.manage', 'guard_name' => 'web', 'label' => 'Manage Users']);
        
        $role = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin']);
        $role->givePermissionTo(['settings.user.view', 'settings.user.manage']);
    }

    private function actingAsAdmin(): static
    {
        $admin = User::factory()->create(['name' => 'System Admin']);
        $admin->assignRole('admin');
        return $this->actingAs($admin);
    }

    /** @test */
    public function it_filters_users_by_column_name_only()
    {
        $name = 'Unique_' . Str::random(10);
        User::factory()->create(['name' => $name, 'email' => 'other@email.com']);
        User::factory()->create(['name' => 'Other Name', 'email' => 'unique@email.com']);

        $this->actingAsAdmin()
            ->get(route('settings.users.index', [
                'search' => $name,
                'search_column' => 'name'
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', $name)
            );
    }

    /** @test */
    public function it_filters_users_by_column_email_only()
    {
        User::factory()->create(['name' => 'John Unique', 'email' => 'other@email.com']);
        User::factory()->create(['name' => 'Other Name', 'email' => 'john.doe@email.com']);

        $this->actingAsAdmin()
            ->get(route('settings.users.index', [
                'search' => 'John',
                'search_column' => 'email'
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.email', 'john.doe@email.com')
            );
    }

    /** @test */
    public function it_respects_per_page_limit()
    {
        User::factory()->count(15)->create();

        $this->actingAsAdmin()
            ->get(route('settings.users.index', ['per_page' => 5]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 5)
                ->where('users.total', 15) // Adjust to root if meta is merged
            );
    }

    /** @test */
    public function it_can_navigate_to_different_pages()
    {
        User::factory()->count(10)->create(['name' => 'AA Patient']);

        $this->actingAsAdmin()
            ->get(route('settings.users.index', ['per_page' => 5, 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 5)
                ->has('users.links')
            );
    }

    /** @test */
    public function it_combines_filters_search_and_role()
    {
        $staffRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'staff_' . Str::random(5), 'guard_name' => 'web', 'label' => 'Staff']);
        
        $name = 'Staff_' . Str::random(10);
        $john = User::factory()->create(['name' => $name]);
        $john->assignRole($staffRole);
        
        User::factory()->create(['name' => 'Other_' . Str::random(10)]);

        $this->actingAsAdmin()
            ->get(route('settings.users.index', [
                'search' => $name,
                'role' => $staffRole->uuid
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', $name)
            );
    }

    /** @test */
    public function it_can_store_a_new_user()
    {
        $role = Role::where('name', 'admin')->first();
        
        $payload = [
            'name' => 'New User Test',
            'email' => 'new-user-test@email.com',
            'password' => 'password123',
            'role_ids' => [$role->uuid],
        ];

        $this->actingAsAdmin()
            ->post(route('settings.users.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'new-user-test@email.com']);
    }

    /** @test */
    public function it_can_update_an_existing_user()
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $role = Role::where('name', 'admin')->first();

        $payload = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role_ids' => [$role->uuid],
        ];

        $this->actingAsAdmin()
            ->put(route('settings.users.update', $user->uuid), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $user = User::factory()->create(['name' => 'User to Delete']);

        $this->actingAsAdmin()
            ->delete(route('settings.users.destroy', $user->uuid))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['uuid' => $user->uuid]);
    }
}
