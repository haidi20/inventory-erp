<?php

namespace Tests\Feature\Settings\Users;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class UserPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['permission.cache.store' => 'array']);
        config(['inertia.testing.ensure_pages_exist' => false]);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Seed basic roles needed for the page
        $admin = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin']);
        $owner = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'owner', 'guard_name' => 'web', 'label' => 'Owner']);
        
        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'settings.user.view',
            'guard_name' => 'web',
            'label' => 'View Users'
        ]);

        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'dashboard.view',
            'guard_name' => 'web',
            'label' => 'View Dashboard'
        ]);

        $owner->givePermissionTo('settings.user.view');
        $owner->givePermissionTo('dashboard.view');
    }

    /** @test */
    public function it_can_render_user_page()
    {
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('owner');

        $this->actingAs($user);

        $response = $this->get(route('settings.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Core/Identity/presentations/views/UserIndex')
            ->has('users')
            ->has('summary')
            ->has('roles')
            ->has('filters')
        );
    }

    /** @test */
    public function it_shows_user_list()
    {
        $owner = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Owner User',
            'email' => 'owner2@example.com',
            'password' => Hash::make('password'),
        ]);
        $owner->assignRole('owner');

        // Create a 'staff' role
        $staffRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'staff', 'guard_name' => 'web', 'label' => 'Staff']);

        $otherUser = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);
        $otherUser->assignRole('staff');

        $this->actingAs($owner);

        $response = $this->get(route('settings.users.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('users.data', fn (Assert $data) => $data
                ->each(fn (Assert $user) => $user
                    ->hasAny('name', 'email', 'uuid')
                    ->etc()
                )
            )
        );
    }

    /** @test */
    public function it_shows_summary_correctly()
    {
        $owner = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Owner User',
            'email' => 'owner4@example.com',
            'password' => Hash::make('password'),
        ]);
        $owner->assignRole('owner');

        $dokterRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'dokter', 'guard_name' => 'web', 'label' => 'Dokter']);
        $staffRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'staff', 'guard_name' => 'web', 'label' => 'Staff']);

        $dokter = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Dr. House',
            'email' => 'house@example.com',
            'password' => Hash::make('password'),
        ]);
        $dokter->assignRole('dokter');

        $staff = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Staff 1',
            'email' => 'staff1@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $this->actingAs($owner);

        $response = $this->get(route('settings.users.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('summary', fn (Assert $summary) => $summary
                ->where('total', 3)
                ->where('dokter', 1)
                ->etc()
            )
        );
    }
}
