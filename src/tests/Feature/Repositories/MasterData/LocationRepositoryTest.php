<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Shift;
use Database\Factories\Master\ShiftFactory;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Repositories\LocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LocationRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LocationRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_locations()
    {
        Location::withoutEvents(function (): void {
            Location::factory()->count(10)->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid
            ]);

            Location::factory()->count(2)->create([
                'branch_uuid' => (string) Str::uuid(),
                'company_uuid' => (string) Str::uuid(),
            ]);
        });

        $entity = new LocationEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
    }

    public function test_save_creates_new_location_and_initializes_stocks()
    {
        // 1. Create some products in the branch first
        Product::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        // 2. Create new location
        $entity = new LocationEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'WH-TEST-001',
            name: 'Gudang Test Utama',
            is_active: true
        );

        $saved = $this->repository->save($entity);

        $this->assertDatabaseHas('locations', [
            'uuid' => $saved->uuid,
            'code' => 'WH-TEST-001'
        ]);

        // 3. Verify automatic stock initialization (must have 3 stock records)
        $stockCount = ProductLocation::where('source_id', $saved->uuid)->where('source_type', 'location')->count();
        $this->assertEquals(3, $stockCount);
    }

    public function test_save_updates_existing_location()
    {
        $location = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'name' => 'Nama Gudang Lama'
        ]);

        $entity = LocationEntity::fromModel($location);
        $entity->name = 'Nama Gudang Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Nama Gudang Baru', $updated->name);
        $this->assertDatabaseHas('locations', [
            'uuid' => $location->uuid,
            'name' => 'Nama Gudang Baru'
        ]);
    }

    public function test_delete_performs_soft_delete_and_allows_reentry()
    {
        $code = 'WH-DEL-001';
        $location = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => $code
        ]);

        $entity = LocationEntity::fromModel($location);
        $this->repository->delete($entity);

        // Verify soft deleted
        $deleted = Location::withTrashed()->where('uuid', $location->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // Verify we can create NEW location with SAME code in SAME branch
        $newEntity = new LocationEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
            name: 'Gudang Reinkarnasi'
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($location->uuid, $reSaved->uuid);
    }

    public function test_find_by_uuid_returns_correct_location()
    {
        $location = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new LocationEntity(
            uuid: $location->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $found = $this->repository->findByUuid($entity);

        $this->assertNotNull($found);
        $this->assertEquals($location->uuid, $found->uuid);
    }

    public function test_get_by_branch_and_code_returns_match_for_branch_and_company(): void
    {
        $code = 'WH-SCOPE-001';

        $expected = Location::withoutEvents(function () use ($code) {
            return Location::factory()->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
                'code' => $code,
            ]);
        });

        $found = $this->repository->getByBranchAndCode(new LocationEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
        ));

        $this->assertNotNull($found);
        $this->assertSame($expected->uuid, $found->uuid);
    }

    public function test_get_active_locations_returns_only_locations_with_shifts()
    {
        $location = Location::factory()->create(['name' => 'Gudang Aktif']);
        ShiftFactory::new()->create(['location_uuid' => $location->uuid]);
        Location::factory()->create(['name' => 'Gudang Tanpa Shift']);

        $result = $this->repository->getActiveLocations();

        $this->assertCount(1, $result);
        $this->assertSame($location->uuid, $result[0]->uuid);
    }

    public function test_list_for_export_scopes_by_company_and_branch()
    {
        $location = Location::factory()->create(['name' => 'Gudang Export']);
        Location::factory()->create(['name' => 'Gudang Lain']);

        $result = $this->repository->listForExport(new LocationEntity(
            company_uuid: $location->company_uuid,
            branch_uuid: $location->branch_uuid,
        ));

        $this->assertCount(1, $result);
        $this->assertSame($location->uuid, $result[0]->uuid);
    }

    public function test_has_shifts_reflects_shift_presence()
    {
        $location = Location::factory()->create();

        $this->assertFalse($this->repository->hasShifts($location->uuid));

        ShiftFactory::new()->create(['location_uuid' => $location->uuid]);

        $this->assertTrue($this->repository->hasShifts($location->uuid));
    }

    public function test_get_branch_options_scopes_by_company()
    {
        $otherCompany = Company::factory()->create();
        Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $result = $this->repository->getBranchOptions($this->company->uuid);

        $this->assertCount(1, $result);
        $this->assertSame($this->branch->uuid, $result[0]['uuid']);
    }

    public function test_get_branch_by_short_name_returns_branch_array()
    {
        $branch = Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
            'short_name'   => 'KDM',
        ]);

        $result = $this->repository->getBranchByShortName('kdm');

        $this->assertNotNull($result);
        $this->assertSame($branch->uuid, $result['uuid']);
    }
}
