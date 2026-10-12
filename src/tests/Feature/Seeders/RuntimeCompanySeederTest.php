<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class RuntimeCompanySeederTest extends TestCase
{
    public function test_it_seeds_sandbox_and_production_companies_with_their_branches(): void
    {
        $this->seed(RuntimeCompanySeeder::class);

        $sandboxCompany = Company::query()->find(RuntimeCompanySeeder::SANDBOX_COMPANY_UUID);
        $productionCompany = Company::query()->find(RuntimeCompanySeeder::PRODUCTION_COMPANY_UUID);

        $this->assertNotNull($sandboxCompany);
        $this->assertSame('Klinik Sandbox Testing', $sandboxCompany->name);
        $this->assertTrue((bool) $sandboxCompany->is_active);

        $this->assertNotNull($productionCompany);
        $this->assertSame('Klinik Production', $productionCompany->name);
        $this->assertTrue((bool) $productionCompany->is_active);

        $sandboxBranch = Branch::query()->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();
        $productionBranch = Branch::query()->where('short_name', RuntimeCompanySeeder::PRODUCTION_BRANCH_SHORT_NAME)->first();

        $this->assertNotNull($sandboxBranch);
        $this->assertSame($sandboxCompany->uuid, $sandboxBranch->company_uuid);
        $this->assertSame('Cabang Sandbox Testing', $sandboxBranch->name);

        $this->assertNotNull($productionBranch);
        $this->assertSame($productionCompany->uuid, $productionBranch->company_uuid);
        $this->assertSame('Cabang Production', $productionBranch->name);
    }

    public function test_it_is_idempotent_when_runtime_company_seeder_is_executed_multiple_times(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(RuntimeCompanySeeder::class);

        $this->assertSame(2, Company::query()->count());
        $this->assertSame(2, Branch::query()->count());
        $this->assertSame(1, Company::query()->where('name', 'Klinik Sandbox Testing')->count());
        $this->assertSame(1, Company::query()->where('name', 'Klinik Production')->count());
        $this->assertSame(1, Branch::query()->where('short_name', 'KSB')->count());
        $this->assertSame(1, Branch::query()->where('short_name', 'KPR')->count());
    }
}
