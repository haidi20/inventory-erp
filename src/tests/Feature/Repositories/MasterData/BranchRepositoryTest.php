<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Repositories\BranchRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BranchRepository $repository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BranchRepository();
        $this->company = Company::factory()->create();
    }

    public function test_get_page_data_returns_paginated_branches()
    {
        Branch::factory()->count(20)->create();

        $entity = new BranchEntity(
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(20, $result['total']);
    }

    public function test_get_page_data_with_search()
    {
        Branch::factory()->create([
            'name' => 'Cabang Jakarta',
            'short_name' => 'JKT'
        ]);

        Branch::factory()->create([
            'name' => 'Cabang Bandung',
            'short_name' => 'BDG'
        ]);

        // Search by name
        $entity = new BranchEntity(search: 'Jakarta');
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Cabang Jakarta', $result['items'][0]->name);

        // Search by short name
        $entity->search = 'BDG';
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Cabang Bandung', $result['items'][0]->name);
    }

    public function test_save_creates_new_branch_and_supports_soft_delete_reentry()
    {
        $shortName = 'TGR';

        // 1. Create and save new branch
        $entity = new BranchEntity(
            company_uuid: $this->company->uuid,
            name: 'Cabang Tangerang',
            short_name: $shortName,
            is_active: true
        );

        $saved = $this->repository->save($entity);
        $this->assertDatabaseHas('branches', ['uuid' => $saved->uuid, 'short_name' => $shortName]);

        // 2. Soft delete the branch
        $this->repository->delete($saved);
        
        // Verify it is soft deleted (deleted_at > 0)
        $deleted = Branch::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // 3. Create NEW branch with SAME short_name (should work because of our composite unique index)
        $newEntity = new BranchEntity(
            company_uuid: $this->company->uuid,
            name: 'Cabang Tangerang Baru',
            short_name: $shortName
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
        $this->assertEquals($shortName, $reSaved->short_name);
    }

    public function test_save_updates_existing_branch()
    {
        $branch = Branch::factory()->create([
            'name' => 'Cabang Lama'
        ]);

        $entity = BranchEntity::fromModel($branch);
        $entity->name = 'Cabang Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Cabang Baru', $updated->name);
        $this->assertDatabaseHas('branches', [
            'uuid' => $branch->uuid,
            'name' => 'Cabang Baru'
        ]);
    }

    public function test_find_by_uuid_returns_correct_branch()
    {
        $branch = Branch::factory()->create();

        $found = $this->repository->findByUuid($branch->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($branch->uuid, $found->uuid);
    }

    public function test_find_first_by_company_returns_first_branch()
    {
        $branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $found = $this->repository->findFirstByCompany($this->company->uuid);

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_find_by_uuid_and_company_returns_matching_branch()
    {
        $branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $found = $this->repository->findByUuidAndCompany($branch->uuid, $this->company->uuid);

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_get_by_short_name_returns_matching_branch()
    {
        $branch = Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
            'short_name' => 'KDM',
        ]);

        $found = $this->repository->getByShortName(new BranchEntity(short_name: 'kdm'));

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_has_warehouses_reflects_location_presence()
    {
        $branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $this->assertFalse($this->repository->hasWarehouses($branch->uuid));

        Location::factory()->create(['branch_uuid' => $branch->uuid]);

        $this->assertTrue($this->repository->hasWarehouses($branch->uuid));
    }

    public function test_list_for_export_scopes_by_company()
    {
        $branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        Branch::factory()->create();

        $result = $this->repository->listForExport(new BranchEntity(
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $result);
        $this->assertSame($branch->uuid, $result[0]->uuid);
    }
}
