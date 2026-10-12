<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\TempLookup;
use App\Modules\Operational\Master\Repositories\LookupSetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LookupSetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LookupSetRepository $repository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LookupSetRepository();
        $this->company = Company::factory()->create();
    }

    public function test_store_set_creates_record()
    {
        $entity = new LookupSetEntity(
            name: 'LOOKUP_CAT_1',
            program_code: 'PC01',
            company_uuid: $this->company->uuid,
        );

        $set = $this->repository->storeSet($entity);

        $this->assertDatabaseHas('lookup_sets', [
            'uuid' => $set->uuid,
            'name' => 'LOOKUP_CAT_1',
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_store_value_creates_record_with_details()
    {
        $set = LookupSet::factory()->create(['company_uuid' => $this->company->uuid]);
        $entity = new LookupSetValueEntity(
            lookup_set_uuid: $set->uuid,
            display_name: 'Value 1',
            active_flag: 'Y',
            company_uuid: $this->company->uuid,
            details: [
                ['idx' => 1, 'value' => 'Detail 1']
            ]
        );

        $value = $this->repository->storeValue($entity);

        // company_uuid kini terisi dari entity (kolom NOT NULL, Section 30).
        $this->assertDatabaseHas('lookup_values', [
            'uuid' => $value->uuid,
            'display_name' => 'Value 1',
            'company_uuid' => $this->company->uuid,
        ]);

        $this->assertDatabaseHas('lookup_value_details', [
            'lookup_value_uuid' => $value->uuid,
            'value' => 'Detail 1'
        ]);
    }

    public function test_get_values_by_set_name()
    {
        $set = LookupSet::factory()->create([
            'name' => 'CATEGORY_X',
            'company_uuid' => '',
        ]);
        LookupValue::factory()->create([
            'lookup_set_uuid' => $set->uuid,
            'active_flag' => 'Y',
            'company_uuid' => $this->company->uuid,
        ]);

        $result = $this->repository->getValuesBySetName(new LookupSetEntity(
            name: 'CATEGORY_X',
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $result->items);
    }

    public function test_get_set_by_uuid_returns_matching_set()
    {
        $set = LookupSet::factory()->create();

        $found = $this->repository->getSetByUuid(new LookupSetEntity(uuid: $set->uuid));

        $this->assertNotNull($found);
        $this->assertSame($set->uuid, $found->uuid);
    }

    public function test_get_set_by_id_returns_matching_set()
    {
        $set = LookupSet::factory()->create();

        $found = $this->repository->getSetById(new LookupSetEntity(id: $set->lookup_set_uuid));

        $this->assertNotNull($found);
        $this->assertSame($set->uuid, $found->uuid);
    }

    public function test_get_set_by_program_code_returns_matching_set()
    {
        $set = LookupSet::factory()->create(['program_code' => 'PC-TEST']);

        $found = $this->repository->getSetByProgramCode(new LookupSetEntity(program_code: 'PC-TEST'));

        $this->assertNotNull($found);
        $this->assertSame($set->uuid, $found->uuid);
    }

    public function test_get_set_by_name_returns_matching_set()
    {
        $set = LookupSet::factory()->create(['name' => 'Kategori Test']);

        $found = $this->repository->getSetByName(new LookupSetEntity(name: 'kategori test'));

        $this->assertNotNull($found);
        $this->assertSame($set->uuid, $found->uuid);
    }

    public function test_update_set_updates_existing_set()
    {
        $set = LookupSet::factory()->create(['name' => 'Lama']);

        $updated = $this->repository->updateSet(new LookupSetEntity(
            uuid: $set->uuid,
            name: 'Baru',
            program_code: 'PC-UPDATE',
        ));

        $this->assertSame('Baru', $updated->name);
        $this->assertDatabaseHas('lookup_sets', [
            'uuid' => $set->uuid,
            'name' => 'Baru',
        ]);
    }

    public function test_destroy_set_soft_deletes_set()
    {
        $set = LookupSet::factory()->create();

        $this->repository->destroySet($set->uuid);

        $this->assertSoftDeleted('lookup_sets', ['uuid' => $set->uuid]);
    }

    public function test_get_set_index_data_returns_paginated_sets()
    {
        LookupSet::factory()->count(3)->create();

        $result = $this->repository->getSetIndexData(new LookupSetEntity(
            start: 0,
            length: 2,
        ));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_get_value_by_uuid_returns_matching_value()
    {
        $value = LookupValue::factory()->create();

        $found = $this->repository->getValueByUuid(new LookupSetValueEntity(uuid: $value->uuid));

        $this->assertNotNull($found);
        $this->assertSame($value->uuid, $found->uuid);
    }

    public function test_update_value_updates_existing_value()
    {
        $value = LookupValue::factory()->create(['display_name' => 'Lama']);

        $updated = $this->repository->updateValue(new LookupSetValueEntity(
            uuid: $value->uuid,
            display_name: 'Baru',
        ));

        $this->assertSame('Baru', $updated->display_name);
        $this->assertDatabaseHas('lookup_values', [
            'uuid' => $value->uuid,
            'display_name' => 'Baru',
        ]);
    }

    public function test_destroy_value_soft_deletes_value()
    {
        $value = LookupValue::factory()->create();

        $this->repository->destroyValue($value->uuid);

        $this->assertSoftDeleted('lookup_values', ['uuid' => $value->uuid]);
    }

    public function test_get_value_index_data_returns_paginated_values()
    {
        $set = LookupSet::factory()->create();
        LookupValue::factory()->count(3)->create(['lookup_set_uuid' => $set->uuid]);

        $result = $this->repository->getValueIndexData(new LookupSetValueEntity(
            lookup_set_uuid: $set->uuid,
            start: 0,
            length: 2,
        ));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_get_values_by_set_id_returns_active_values()
    {
        $set = LookupSet::factory()->create();
        LookupValue::factory()->count(2)->create([
            'lookup_set_uuid' => $set->uuid,
            'active_flag' => 'Y',
        ]);

        $result = $this->repository->getValuesBySetId(new LookupSetEntity(uuid: $set->uuid));

        $this->assertCount(2, $result->items);
    }

    public function test_upsert_value_updates_existing_value()
    {
        $set = LookupSet::factory()->create();
        LookupValue::factory()->create([
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Existing',
        ]);

        $result = $this->repository->upsertValue(new LookupSetValueEntity(
            lookup_set_uuid: $set->uuid,
            display_name: 'Existing',
            description: 'Updated',
        ));

        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Existing',
            'description' => 'Updated',
        ]);
    }

    public function test_sync_temp_to_master_moves_temp_values()
    {
        $set = LookupSet::factory()->create();
        TempLookup::create([
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
            'seq' => 1,
            'active_flag' => 'Y',
        ]);

        $this->repository->syncTempToMaster($set->uuid);

        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
        ]);
        $this->assertDatabaseMissing('temp_lovs', [
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
        ]);
    }

    public function test_clear_temp_values_removes_temp_records()
    {
        $set = LookupSet::factory()->create();
        TempLookup::create([
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
        ]);

        $this->repository->clearTempValues($set->uuid);

        $this->assertDatabaseMissing('temp_lovs', [
            'lookup_set_uuid' => $set->uuid,
        ]);
    }
}
