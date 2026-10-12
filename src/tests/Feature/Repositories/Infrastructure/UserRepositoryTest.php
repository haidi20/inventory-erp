<?php

namespace Tests\Feature\Repositories\Infrastructure;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Repositories\UserRepository;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;
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

        $this->repository = new UserRepository();

        $this->company = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Sandbox Company',
            'clinic_type' => 'Klinik',
            'phone' => '0411000001',
            'email' => 'sandbox-company@test.local',
            'address' => 'Sandbox Street',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        $this->otherCompany = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Company',
            'clinic_type' => 'Klinik',
            'phone' => '0411000002',
            'email' => 'other-company@test.local',
            'address' => 'Other Street',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        // Setup real branch for foreign key constraints
        $this->branch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Main Branch',
            'short_name' => 'MB01',
            'company_uuid' => $this->company->uuid,
            'is_active' => true,
        ]);

        $this->otherBranch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Branch',
            'short_name' => 'OB01',
            'company_uuid' => $this->otherCompany->uuid,
            'is_active' => true,
        ]);

        // Setup basic roles
        $this->adminRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin']);
        $this->staffRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'staff', 'guard_name' => 'web', 'label' => 'Staff']);
        $this->dokterRole = Role::create(['uuid' => (string) Str::uuid(), 'name' => 'dokter', 'guard_name' => 'web', 'label' => 'Dokter']);
    }

    /** @test */
    public function it_can_paginate_users_with_real_filtering(): void
    {
        // Create 3 users
        $user1 = User::factory()->create([
            'name' => 'Andi User',
            'email' => 'andi@test.com',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $user1->assignRole($this->staffRole);
        
        $user2 = User::factory()->create([
            'name' => 'Budi Dokter',
            'email' => 'budi@test.com',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $user2->assignRole($this->dokterRole);

        // Admin (should be excluded by repository logic typically)
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $admin->assignRole($this->adminRole);

        $foreignUser = User::factory()->create([
            'name' => 'Foreign User',
            'email' => 'foreign@test.com',
            'branch_uuid' => $this->otherBranch->uuid,
            'company_uuid' => $this->otherCompany->uuid,
        ]);
        $foreignUser->assignRole($this->staffRole);

        // 1. Test Search
        $entity = UserEntity::fromArray([
            'search' => 'Andi',
            'per_page' => 10,
            'company_uuid' => $this->company->uuid,
        ]);
        $results = $this->repository->paginate($entity);
        $this->assertEquals(1, $results->total());
        $this->assertEquals('Andi User', $results->items()[0]->name);

        // 2. Test Role Filter
        $entity = UserEntity::fromArray([
            'role' => $this->dokterRole->uuid,
            'per_page' => 10,
            'company_uuid' => $this->company->uuid,
        ]);
        $results = $this->repository->paginate($entity);
        $this->assertEquals(1, $results->total());
        $this->assertEquals('Budi Dokter', $results->items()[0]->name);
    }

    /** @test */
    public function it_can_find_user_by_uuid_with_relations(): void
    {
        $user = User::factory()->create([
            'name' => 'Find Me',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $user->assignRole($this->staffRole);

        $found = $this->repository->findByUuid($user->uuid);

        $this->assertNotNull($found);
        $this->assertEquals('Find Me', $found->name);
        $this->assertSame($this->company->uuid, $found->company_uuid);
        $this->assertSame($this->staffRole->uuid, $found->role_uuid);
    }

    /** @test */
    public function it_can_find_user_by_email_case_insensitive(): void
    {
        User::factory()->create([
            'email' => 'CASE@test.com',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->findByEmail('case@test.com');

        $this->assertNotNull($found);
        $this->assertEquals('CASE@test.com', $found->email);
    }

    /** @test */
    public function it_can_save_new_user_to_real_database(): void
    {
        $entity = UserEntity::fromArray([
            'name' => 'Repository User',
            'email' => 'repo@test.com',
            'password' => 'secret123',
            'role_uuid' => $this->staffRole->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $user = $this->repository->save($entity);

        // Kolom legacy company_uuid sudah di-drop dari users; kaitan company
        // kini tersimpan di pivot user_companies (is_default=true).
        $this->assertDatabaseHas('users', [
            'email' => 'repo@test.com',
        ]);
        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        $this->assertSame($this->company->uuid, $user->company_uuid);
    }

    /** @test */
    public function it_can_update_existing_user_and_sync_roles(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $user->assignRole($this->staffRole);

        $entity = UserEntity::fromArray([
            'uuid' => $user->uuid,
            'name' => 'New Name',
            'email' => $user->email,
            'role_uuid' => $this->dokterRole->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $updatedUser = $this->repository->save($entity);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertSame($this->company->uuid, $updatedUser->company_uuid);
    }

    /** @test */
    public function it_can_soft_delete_user_and_suffix_email_to_avoid_collisions(): void
    {
        $user = User::factory()->create([
            'email' => 'target@test.com',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        
        $this->repository->delete(UserEntity::fromArray([
            'uuid' => $user->uuid,
            'actor_uuid' => (string) Str::uuid(),
            'company_uuid' => $this->company->uuid,
        ]));

        $this->assertSoftDeleted('users', ['uuid' => $user->uuid]);
        
        $deleted = User::withTrashed()->where('uuid', $user->uuid)->first();
        $this->assertStringContainsString('target@test.com-del-', $deleted->email);
    }

    /** @test */
    public function it_can_get_summary_data_correctly(): void
    {
        // 2 staff, 1 dokter
        User::factory()->count(2)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ])->each(fn($u) => $u->assignRole($this->staffRole));
        User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ])->assignRole($this->dokterRole);
        // 1 admin (should be excluded)
        User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ])->assignRole($this->adminRole);

        User::factory()->create([
            'branch_uuid' => $this->otherBranch->uuid,
            'company_uuid' => $this->otherCompany->uuid,
        ])->assignRole($this->staffRole);

        $summary = $this->repository->getSummaryData($this->company->uuid);

        $this->assertEquals(3, $summary['total']); // Total non-admin: 2 staff + 1 dokter
        $this->assertEquals(1, $summary['dokter']);
        $this->assertEquals(2, $summary['other']);
    }

    /** @test */
    public function it_can_get_all_roles_excluding_admin(): void
    {
        $roles = $this->repository->getAllRoles();

        // Should return staff and dokter, but not admin
        $this->assertCount(2, $roles);
        $this->assertFalse($roles->pluck('name')->contains('admin'));
    }
}
