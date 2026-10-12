<?php

namespace Tests\Feature\Settings\Users;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserInertiaCrudRealDbTest extends TestCase
{
    private User $adminUser;
    private Role $adminRole;
    private Role $staffRole;
    private Role $dokterRole;
    private Company $company;
    private Company $otherCompany;
    private Branch $branch;
    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();

        \Inertia\Inertia::setRootView('layouts.app');
        config(['inertia.testing.ensure_pages_exist' => false]);
        config(['permission.cache.store' => 'array']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'settings.view',
            'guard_name' => 'web',
            'label' => 'View Settings',
        ]);
        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'settings.user.view',
            'guard_name' => 'web',
            'label' => 'View Users',
        ]);
        Permission::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'settings.user.manage',
            'guard_name' => 'web',
            'label' => 'Manage Users',
        ]);

        $this->company = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Sandbox Company',
            'clinic_type' => 'Klinik',
            'phone' => '0411000011',
            'email' => 'sandbox@settings-user.test',
            'address' => 'Sandbox',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        $this->otherCompany = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Company',
            'clinic_type' => 'Klinik',
            'phone' => '0411000012',
            'email' => 'other@settings-user.test',
            'address' => 'Other',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Sandbox Branch',
            'short_name' => 'SBX',
            'company_uuid' => $this->company->uuid,
            'is_active' => true,
        ]);

        $this->otherBranch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Branch',
            'short_name' => 'OTH',
            'company_uuid' => $this->otherCompany->uuid,
            'is_active' => true,
        ]);

        $this->adminRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'admin',
            'guard_name' => 'web',
            'label' => 'Admin',
        ]);
        $this->adminRole->givePermissionTo([
            'settings.view',
            'settings.user.view',
            'settings.user.manage',
        ]);

        $this->staffRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'staff',
            'guard_name' => 'web',
            'label' => 'Staff',
        ]);
        $this->dokterRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'dokter',
            'guard_name' => 'web',
            'label' => 'Dokter',
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Testing',
            'email' => 'admin.testing@klinik.local',
            'password' => Hash::make('password'),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->adminUser->assignRole($this->adminRole);
    }

    /** @test */
    public function read_index_returns_inertia_payload_from_real_database(): void
    {
        $dokter = User::factory()->create([
            'name' => 'Dokter A',
            'email' => 'dokter-a@klinik.local',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $dokter->assignRole($this->dokterRole);

        $staff = User::factory()->create([
            'name' => 'Staff B',
            'email' => 'staff-b@klinik.local',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $staff->assignRole($this->staffRole);

        $foreignUser = User::factory()->create([
            'name' => 'Foreign Staff',
            'email' => 'foreign-staff@klinik.local',
            'branch_uuid' => $this->otherBranch->uuid,
            'company_uuid' => $this->otherCompany->uuid,
        ]);
        $foreignUser->assignRole($this->staffRole);

        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.users.index', [
                'search' => 'Dokter',
                'search_column' => 'name',
                'role' => $this->dokterRole->uuid,
                'per_page' => 10,
            ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Core/Identity/presentations/views/UserIndex')
            ->where('filters.search', 'Dokter')
            ->where('filters.search_column', 'name')
            ->where('filters.role', $this->dokterRole->uuid)
            ->has('roles', 2)
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Dokter A')
            ->where('summary.total', 2)
            ->where('summary.dokter', 1)
            ->where('summary.other', 1)
        );
    }

    /** @test */
    public function create_stores_real_user_record_and_assigns_role(): void
    {
        $payload = [
            'name' => 'User CRUD Baru',
            'email' => 'crud-baru@klinik.local',
            'password' => 'Password123!',
            'role_ids' => [$this->staffRole->uuid],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('settings.users.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        // Kolom legacy company_uuid sudah di-drop dari users; kaitan company
        // user baru kini tersimpan di pivot user_companies.
        $this->assertDatabaseHas('users', [
            'name' => 'User CRUD Baru',
            'email' => 'crud-baru@klinik.local',
        ]);

        $createdUser = User::where('email', 'crud-baru@klinik.local')->firstOrFail();
        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $createdUser->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->assertTrue($createdUser->hasRole('staff'));
    }

    /** @test */
    public function update_changes_real_user_record_in_database(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'nama-lama@klinik.local',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $targetUser->assignRole($this->staffRole);

        $payload = [
            'name' => 'Nama Baru',
            'email' => 'nama-baru@klinik.local',
            'password' => '',
            'role_ids' => [$this->dokterRole->uuid],
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('settings.users.update', $targetUser->uuid), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertSame('Nama Baru', $targetUser->name);
        $this->assertSame('nama-baru@klinik.local', $targetUser->email);
        $this->assertTrue($targetUser->hasRole('dokter'));
    }

    /** @test */
    public function delete_soft_deletes_real_user_record_and_keeps_current_user_safe(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Hapus Saya',
            'email' => 'hapus-saya@klinik.local',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $targetUser->assignRole($this->staffRole);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('settings.users.destroy', $targetUser->uuid));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['uuid' => $targetUser->uuid]);

        $deletedUser = User::withTrashed()->where('uuid', $targetUser->uuid)->firstOrFail();
        $this->assertStringContainsString('hapus-saya@klinik.local-del-', $deletedUser->email);

        $selfDeleteResponse = $this->actingAs($this->adminUser)
            ->delete(route('settings.users.destroy', $this->adminUser->uuid));

        $selfDeleteResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'uuid' => $this->adminUser->uuid,
            'deleted_at' => 0,
        ]);
    }

    /** @test */
    public function tdd_guard_fails_validation_when_payload_is_invalid(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->from(route('settings.users.index'))
            ->post(route('settings.users.store'), [
                'name' => '',
                'email' => 'email-tidak-valid',
                'password' => '',
                'role_ids' => [],
            ]);

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'role_ids',
        ]);
    }
}
