<?php

namespace Tests\Concerns;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Database\Seeders\V1\RuntimeUserSeeder;
use Illuminate\Support\Facades\Auth;

trait InteractsWithSandboxRuntime
{
    protected function sandboxCompany(): Company
    {
        $this->seed(RuntimeCompanySeeder::class);

        return Company::query()->findOrFail(RuntimeCompanySeeder::SANDBOX_COMPANY_UUID);
    }

    protected function sandboxBranch(): Branch
    {
        $company = $this->sandboxCompany();

        return Branch::query()->firstOrCreate(
            ['short_name' => RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME],
            [
                'name' => 'Cabang Sandbox Testing',
                'description' => 'Cabang khusus sandbox, staging, dan testing otomatis.',
                'company_uuid' => $company->uuid,
                'is_active' => true,
            ],
        );
    }

    protected function sandboxOwner(): User
    {
        $this->sandboxBranch();
        $this->seed(RuntimeUserSeeder::class);

        return User::query()->where('email', 'owner@klinik.local')->firstOrFail();
    }

    protected function demoCompany(): Company
    {
        return $this->sandboxCompany();
    }

    protected function demoBranch(): Branch
    {
        $company = $this->sandboxCompany();

        return Branch::query()->firstOrCreate(
            ['short_name' => RuntimeCompanySeeder::DEMO_BRANCH_SHORT_NAME],
            [
                'name' => 'Klinik Demo',
                'description' => 'Cabang demo untuk testing CRUD.',
                'company_uuid' => $company->uuid,
                'is_active' => true,
            ],
        );
    }

    protected function demoSuperadmin(): User
    {
        $branch = $this->demoBranch();
        $role = Role::query()->firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['label' => 'Super Administrator'],
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'superadmin@klinik.local'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => 'superadmin',
                'is_active' => true,
            ],
        );
        $user->username = 'superadmin';
        $user->save();
        $user->assignRole($role);

        UserCompany::query()->updateOrCreate(
            ['user_uuid' => $user->uuid, 'company_uuid' => $branch->company_uuid],
            ['is_default' => true],
        );
        UserBranch::query()->updateOrCreate(
            [
                'user_uuid' => $user->uuid,
                'company_uuid' => $branch->company_uuid,
                'branch_uuid' => $branch->uuid,
            ],
            ['is_default' => true],
        );

        return $user;
    }

    protected function actingAsDemoSuperadmin(): User
    {
        $user = $this->demoSuperadmin();
        $this->actingAs($user);
        Auth::shouldUse('web');

        return $user;
    }
}
