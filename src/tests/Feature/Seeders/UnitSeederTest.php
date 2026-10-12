<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Unit;
use Database\Seeders\V1\UnitSeeder;
use Tests\TestCase;

class UnitSeederTest extends TestCase
{
    public function test_it_uses_branch_company_uuid_for_seeded_units(): void
    {
        $company = Company::factory()->create([
            'uuid' => '55555555-5555-4555-8555-555555555555',
            'name' => 'Klinik Seed Test Unit',
        ]);

        $branch = Branch::factory()->create([
            'uuid' => '66666666-6666-4666-8666-666666666666',
            'short_name' => 'KDM',
            'name' => 'Cabang Demo Unit',
            'company_uuid' => $company->uuid,
        ]);

        $this->seed(UnitSeeder::class);

        $pcs = Unit::query()->where('code', 'PCS')->first();
        $tablet = Unit::query()->where('code', 'TABLET')->first();

        $this->assertNotNull($pcs);
        $this->assertSame($branch->company_uuid, $pcs->company_uuid);

        $this->assertNotNull($tablet);
        $this->assertSame($branch->company_uuid, $tablet->company_uuid);
    }
}
