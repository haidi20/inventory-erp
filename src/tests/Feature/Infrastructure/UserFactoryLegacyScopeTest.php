<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kolom legacy branch_uuid/company_uuid sudah di-drop dari tabel users.
 * Model User menyediakan shim kompatibilitas: bacaan proxy ke context aktif
 * (pivot), penulisan/mass-assignment diterjemahkan menjadi pivot is_default
 * -- dipakai luas oleh test lama via User::factory()->create(['branch_uuid' => ...]).
 */
class UserFactoryLegacyScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_branch_uuid_membuat_pivot_dan_active_context(): void
    {
        $company = Company::create(['name' => 'Co', 'is_active' => true]);
        $branch = Branch::create([
            'name' => 'Cabang A',
            'short_name' => 'CBA',
            'company_uuid' => $company->uuid,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['branch_uuid' => $branch->uuid]);

        $this->assertTrue(
            UserCompany::query()->where('user_uuid', $user->uuid)->where('company_uuid', $company->uuid)->where('is_default', true)->exists()
        );
        $this->assertTrue(
            UserBranch::query()->where('user_uuid', $user->uuid)->where('branch_uuid', $branch->uuid)->where('is_default', true)->exists()
        );
        $this->assertSame($company->uuid, $user->active_company_uuid);
        $this->assertSame($branch->uuid, $user->active_branch_uuid);
        $this->assertSame($branch->uuid, $user->branch_uuid);
        $this->assertSame($company->uuid, $user->company_uuid);
    }

    public function test_factory_company_uuid_saja_hanya_membuat_pivot_company(): void
    {
        $company = Company::create(['name' => 'Co HQ', 'is_active' => true]);

        $user = User::factory()->create(['company_uuid' => $company->uuid]);

        $this->assertTrue(
            UserCompany::query()->where('user_uuid', $user->uuid)->where('company_uuid', $company->uuid)->where('is_default', true)->exists()
        );
        $this->assertSame($company->uuid, $user->active_company_uuid);
        $this->assertNull($user->active_branch_uuid);
    }

    public function test_factory_tanpa_legacy_tidak_membuat_pivot(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(UserCompany::query()->where('user_uuid', $user->uuid)->exists());
        $this->assertFalse(UserBranch::query()->where('user_uuid', $user->uuid)->exists());
        $this->assertNull($user->active_company_uuid);
    }

    public function test_factory_count_menerapkan_pivot_ke_semua_user(): void
    {
        $company = Company::create(['name' => 'Co Multi', 'is_active' => true]);
        $branch = Branch::create([
            'name' => 'Cabang M',
            'short_name' => 'CBM',
            'company_uuid' => $company->uuid,
            'is_active' => true,
        ]);

        $users = User::factory()->count(3)->create(['branch_uuid' => $branch->uuid]);

        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertSame($company->uuid, $user->active_company_uuid);
            $this->assertSame($branch->uuid, $user->active_branch_uuid);
        }
        $this->assertSame(3, UserBranch::query()->where('branch_uuid', $branch->uuid)->count());
    }

    public function test_user_create_mass_assignment_legacy_kolom(): void
    {
        $company = Company::create(['name' => 'Co C', 'is_active' => true]);
        $branch = Branch::create([
            'name' => 'Cabang C',
            'short_name' => 'CBC',
            'company_uuid' => $company->uuid,
            'is_active' => true,
        ]);

        // Pola test lama: User::create([... 'branch_uuid' => ..., 'company_uuid' => ...])
        $user = User::create([
            'name' => 'Legacy User',
            'email' => 'legacy@test.local',
            'password' => bcrypt('password'),
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);

        $this->assertSame($branch->uuid, $user->branch_uuid);
        $this->assertSame($company->uuid, $user->company_uuid);
        $this->assertTrue(
            UserBranch::query()->where('user_uuid', $user->uuid)->where('branch_uuid', $branch->uuid)->exists()
        );
    }

    public function test_assignment_branch_uuid_pada_user_tersimpan_membuat_pivot(): void
    {
        $company = Company::create(['name' => 'Co D', 'is_active' => true]);
        $branch = Branch::create([
            'name' => 'Cabang D',
            'short_name' => 'CBD',
            'company_uuid' => $company->uuid,
            'is_active' => true,
        ]);

        // Pola test lama: $user->branch_uuid = X; $user->save();
        $user = User::factory()->create();
        $user->branch_uuid = $branch->uuid;
        $user->save();

        $this->assertSame($branch->uuid, $user->branch_uuid);
        $this->assertSame($company->uuid, $user->active_company_uuid);
        $this->assertTrue(
            UserBranch::query()->where('user_uuid', $user->uuid)->where('branch_uuid', $branch->uuid)->where('is_default', true)->exists()
        );
    }
}
