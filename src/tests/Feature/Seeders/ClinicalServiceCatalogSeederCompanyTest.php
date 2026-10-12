<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use Database\Seeders\V1\ClinicalServiceCatalogSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class ClinicalServiceCatalogSeederCompanyTest extends TestCase
{
    public function test_it_scopes_services_and_packages_to_the_demo_branch_company(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(ClinicalServiceCatalogSeeder::class);

        $demoBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $this->assertDatabaseHas('services', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'code' => 'SRV-POL-001',
        ]);

        $this->assertDatabaseHas('service_packages', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'code' => 'PKG-MCU-001',
        ]);

        $this->assertDatabaseHas('service_package_items', [
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
            'item_type' => 'service',
        ]);
    }
}
