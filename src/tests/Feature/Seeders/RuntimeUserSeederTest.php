<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\RolePermissionSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Database\Seeders\V1\RuntimeUserSeeder;
use Tests\TestCase;

class RuntimeUserSeederTest extends TestCase
{
    public function test_it_seeds_runtime_users_into_the_sandbox_branch_with_expected_roles(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(RuntimeUserSeeder::class);

        $sandboxBranch = Branch::query()->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->firstOrFail();

        $owner = User::query()->where('email', 'owner@klinik.local')->first();
        $admin = User::query()->where('email', 'admin@klinik.local')->first();
        $kasir = User::query()->where('email', 'kasir@klinik.local')->first();

        $this->assertNotNull($owner);
        $this->assertSame($sandboxBranch->uuid, $owner->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $owner->company_uuid);
        $this->assertTrue($owner->hasRole('owner'));

        $this->assertNotNull($admin);
        $this->assertSame($sandboxBranch->uuid, $admin->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $admin->company_uuid);
        $this->assertTrue($admin->hasRole('admin'));

        $this->assertNotNull($kasir);
        $this->assertSame($sandboxBranch->uuid, $kasir->branch_uuid);
        $this->assertSame($sandboxBranch->company_uuid, $kasir->company_uuid);
        $this->assertTrue($kasir->hasRole('kasir'));
    }
}
