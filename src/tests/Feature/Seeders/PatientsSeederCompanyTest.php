<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use Database\Seeders\V1\PatientsSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Tests\TestCase;

class PatientsSeederCompanyTest extends TestCase
{
    public function test_it_scopes_seeded_patients_to_demo_or_sandbox_branch_company(): void
    {
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(PatientsSeeder::class);

        $branch = Branch::query()
            ->whereIn('short_name', ['KDM', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME])
            ->orderByRaw("CASE WHEN short_name = 'KDM' THEN 0 ELSE 1 END")
            ->firstOrFail();

        $this->assertDatabaseHas('patients', [
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $branch->company_uuid,
        ]);
    }
}
