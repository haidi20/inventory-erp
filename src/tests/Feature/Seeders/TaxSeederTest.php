<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Tax;
use Database\Seeders\V1\TaxSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_taxes_from_ninesoft(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Seed Test Tax']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(TaxSeeder::class);

        $ppn = Tax::query()->where('tax_code', 'PPN')->first();
        $this->assertNotNull($ppn, 'PPN harus ada setelah seeding');
        $this->assertSame(12.0, (float) $ppn->percentage);
        $this->assertTrue((bool) $ppn->is_primary);

        $noPpn = Tax::query()->where('tax_code', 'NO PPN')->first();
        $this->assertNotNull($noPpn);
        $this->assertSame(0.0, (float) $noPpn->percentage);
    }

    public function test_seeder_creates_all_9_taxes(): void
    {
        $company = Company::factory()->create();
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(TaxSeeder::class);

        $this->assertGreaterThanOrEqual(9, Tax::query()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $company = Company::factory()->create();
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(TaxSeeder::class);
        $first = Tax::query()->count();
        $this->seed(TaxSeeder::class);
        $second = Tax::query()->count();

        $this->assertSame($first, $second);
    }
}