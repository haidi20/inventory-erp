<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use Database\Seeders\V1\LookupSeeder;
use Tests\TestCase;

class LookupSeederTest extends TestCase
{
    public function test_it_keeps_lookup_sets_global_and_scopes_lookup_values_by_company(): void
    {
        $company = Company::factory()->create([
            'uuid' => '33333333-3333-4333-8333-333333333333',
            'name' => 'Klinik Seed Test Lookup',
        ]);

        $branch = Branch::factory()->create([
            'uuid' => '44444444-4444-4444-8444-444444444444',
            'short_name' => 'KDM',
            'name' => 'Cabang Demo Lookup',
            'company_uuid' => $company->uuid,
        ]);

        $this->seed(LookupSeeder::class);

        $genderSet = LookupSet::query()->where('program_code', 'GENDER')->first();
        $maleValue = LookupValue::query()->where('program_code', 'MALE')->first();

        $this->assertNotNull($genderSet);
        $this->assertSame('', $genderSet->company_uuid);

        $this->assertNotNull($maleValue);
        $this->assertSame($branch->company_uuid, $maleValue->company_uuid);
    }
}
