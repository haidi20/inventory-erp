<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Repositories\UnitRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UnitRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UnitRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_units()
    {
        Unit::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new UnitEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
        $this->assertInstanceOf(UnitEntity::class, $result['items'][0]);
    }

    public function test_get_page_data_with_search()
    {
        Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'code' => 'PCS',
            'name' => 'Pieces',
            'company_uuid' => $this->company->uuid,
        ]);

        Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'code' => 'BOX',
            'name' => 'Kotak',
            'company_uuid' => $this->company->uuid,
        ]);

        // Search by code
        $entity = new UnitEntity(
            search: 'PCS',
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        );
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('PCS', $result['items'][0]->code);

        // Search by name
        $entity->search = 'Kotak';
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('BOX', $result['items'][0]->code);
    }

    public function test_save_creates_new_unit_and_supports_soft_delete_reentry()
    {
        $code = 'STRIP';

        // 1. Create and save new unit
        $entity = new UnitEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
            name: 'Strip Obat',
            is_active: true
        );

        $saved = $this->repository->save($entity);
        $this->assertDatabaseHas('units', ['uuid' => $saved->uuid, 'code' => $code]);

        // 2. Soft delete the unit
        $this->repository->delete($saved);
        
        // Verify it is soft deleted (deleted_at > 0)
        $deleted = Unit::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // 3. Create NEW unit with SAME code in SAME branch (should work because of our composite unique index)
        $newEntity = new UnitEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
            name: 'Strip Obat Reinkarnasi'
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
        $this->assertEquals($code, $reSaved->code);
    }

    public function test_save_updates_existing_unit()
    {
        $unit = Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'name' => 'Satuan Lama'
        ]);

        $entity = UnitEntity::fromModel($unit);
        $entity->name = 'Satuan Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Satuan Baru', $updated->name);
        $this->assertDatabaseHas('units', [
            'uuid' => $unit->uuid,
            'name' => 'Satuan Baru'
        ]);
    }

    public function test_find_by_uuid_returns_correct_unit()
    {
        $unit = Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new UnitEntity(
            uuid: $unit->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $found = $this->repository->findByUuid($entity);

        $this->assertNotNull($found);
        $this->assertEquals($unit->uuid, $found->uuid);
    }

    public function test_get_by_code_returns_unit_from_same_branch_and_company()
    {
        $unit = Unit::factory()->create([
            'code' => 'BOX-TEST',
        ]);

        $found = $this->repository->getByCode(new UnitEntity(
            code: 'box-test',
            branch_uuid: $unit->branch_uuid,
            company_uuid: $unit->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($unit->uuid, $found->uuid);
    }
}
