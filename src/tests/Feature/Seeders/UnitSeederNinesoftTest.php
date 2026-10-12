<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Unit;
use Database\Seeders\V1\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitSeederNinesoftTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_contains_ninesoft_units(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Seed Test Unit Ninesoft']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(UnitSeeder::class);

        foreach (['KG', 'JAM', 'PCS', 'ROLL', 'SET', 'PACK', 'ML', 'KRG', 'KTK', 'LBR'] as $code) {
            $this->assertNotNull(
                Unit::query()->where('code', $code)->first(),
                "Satuan {$code} harus ada dari data Ninesoft"
            );
        }
    }

    public function test_seeder_contains_klinik_units(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Seed Unit Klinik']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(UnitSeeder::class);

        foreach (['TABLET', 'KAPSUL', 'STRIP', 'VIAL', 'AMPUL', 'SACHET'] as $code) {
            $this->assertNotNull(
                Unit::query()->where('code', $code)->first(),
                "Satuan klinik {$code} harus ada"
            );
        }
    }

    public function test_seeder_minimum_unit_count(): void
    {
        $company = Company::factory()->create();
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(UnitSeeder::class);

        $this->assertGreaterThanOrEqual(25, Unit::query()->count(), 'Total satuan harus minimal 25');
    }
}