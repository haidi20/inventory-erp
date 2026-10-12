<?php

namespace Tests\Feature\Repositories\Core\Identity;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Repositories\UserRepository;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserPivotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_uses_company_and_branch_pivots_without_legacy_columns(): void
    {
        $this->assertFalse(Schema::hasColumns('users', ['branch_uuid', 'company_uuid']));

        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $user = app(UserRepository::class)->save(UserEntity::fromArray([
            'name' => 'Pivot User',
            'email' => 'pivot-user@test.local',
            'password' => 'secret123',
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]));

        $this->assertSame($company->uuid, $user->company_uuid);
        $this->assertSame($branch->uuid, $user->branch_uuid);
        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('user_branches', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid' => $branch->uuid,
            'is_default' => true,
        ]);

        $secondCompany = Company::factory()->create();
        $secondBranch = Branch::factory()->create(['company_uuid' => $secondCompany->uuid]);
        app(UserRepository::class)->save(UserEntity::fromArray([
            'uuid' => $user->uuid,
            'name' => 'Pivot User',
            'email' => 'pivot-user@test.local',
            'password' => 'secret123',
            'branch_uuid' => $secondBranch->uuid,
            'company_uuid' => $secondCompany->uuid,
        ]));

        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('user_companies', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $secondCompany->uuid,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('user_branches', [
            'user_uuid' => $user->uuid,
            'company_uuid' => $secondCompany->uuid,
            'branch_uuid' => $secondBranch->uuid,
            'is_default' => true,
        ]);
    }
}
