<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Location;
use Database\Seeders\V1\WarehouseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_warehouses_with_correct_company(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Seed Test Warehouse']);
        Branch::factory()->create([
            'short_name'   => 'KDM',
            'name'         => 'Cabang Demo Warehouse',
            'company_uuid' => $company->uuid,
        ]);

        $this->seed(WarehouseSeeder::class);

        $pusat = Location::query()->where('code', 'PUSAT')->first();
        $this->assertNotNull($pusat, 'Gudang PUSAT harus ada setelah seeding');
        $this->assertSame($company->uuid, $pusat->company_uuid);
    }

    public function test_seeder_creates_konsinyasi_warehouse(): void
    {
        $company = Company::factory()->create(['name' => 'Test Konsinyasi']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(WarehouseSeeder::class);

        $konsinyasi = Location::query()->where('code', 'KONSINYASI')->first();
        $this->assertNotNull($konsinyasi, 'Gudang KONSINYASI harus ada');
        $this->assertFalse((bool) $konsinyasi->can_sell);
    }

    public function test_seeder_is_idempotent(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Idempotent Warehouse']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(WarehouseSeeder::class);
        $countFirst = Location::query()->count();

        $this->seed(WarehouseSeeder::class);
        $countSecond = Location::query()->count();

        $this->assertSame($countFirst, $countSecond, 'Seeder tidak boleh duplikasi data');
    }
}