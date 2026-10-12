<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\LookupType;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Repositories\LookupRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LookupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LookupRepository $repository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LookupRepository();
        $this->company = Company::factory()->create();
    }

    public function test_get_types_returns_paginated_data()
    {
        LookupType::factory()->count(10)->create(['company_uuid' => $this->company->uuid]);

        $entity = new LookupTypeEntity(per_page: 5, page: 1, company_uuid: $this->company->uuid);
        $result = $this->repository->getTypesPageData($entity);

        $this->assertCount(5, $result->items);
        $this->assertEquals(10, $result->total);
        $this->assertInstanceOf(LookupTypeEntity::class, $result->items[0]);
    }

    public function test_save_type_creates_new_and_supports_soft_delete_reentry()
    {
        $code = 'BLOOD_TYPE';

        // 1. Create and save new type
        $entity = new LookupTypeEntity(
            code: $code,
            name: 'Golongan Darah',
            company_uuid: $this->company->uuid
        );

        $saved = $this->repository->saveType($entity);
        $this->assertDatabaseHas('lookup_types', ['uuid' => $saved->uuid, 'code' => $code]);

        // 2. Soft delete the type
        $model = LookupType::where('uuid', $saved->uuid)->first();
        $model->delete();
        
        $deleted = LookupType::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);

        // 3. Re-create with SAME code (Must work)
        $newEntity = new LookupTypeEntity(
            code: $code,
            name: 'Golongan Darah (Baru)',
            company_uuid: $this->company->uuid
        );

        $reSaved = $this->repository->saveType($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
    }

    public function test_save_value_creates_and_initializes_correctly()
    {
        $type = LookupType::factory()->create(['code' => 'GENDER', 'company_uuid' => $this->company->uuid]);

        $entity = new LookupValueEntity(
            lookup_type_uuid: $type->uuid,
            code: 'MALE',
            value: 'Laki-laki',
            order_priority: 1,
            company_uuid: $this->company->uuid
        );

        $saved = $this->repository->saveValue($entity);

        $this->assertDatabaseHas('lookup_values', [
            'uuid' => $saved->uuid,
            'lookup_set_uuid' => $type->uuid,
            'code' => 'MALE'
        ]);
    }

    public function test_get_values_by_type_works_correctly()
    {
        // getValuesByType queries whereHas('lookupSet') which filters LookupSet by code.
        // LookupValue.lookup_set_uuid FK -> lookup_sets.uuid (not lookup_types).
        $set = LookupSet::factory()->create(['code' => 'MARITAL_STATUS', 'company_uuid' => $this->company->uuid]);

        LookupValue::factory()->create([
            'lookup_set_uuid' => $set->uuid,
            'code' => 'SINGLE',
            'value' => 'Lajang',
            'display_name' => 'Lajang',
            'order_priority' => 2,
            'company_uuid' => $this->company->uuid,
        ]);

        LookupValue::factory()->create([
            'lookup_set_uuid' => $set->uuid,
            'code' => 'MARRIED',
            'value' => 'Menikah',
            'display_name' => 'Menikah',
            'order_priority' => 1,
            'company_uuid' => $this->company->uuid,
        ]);

        $typeEntity = new LookupTypeEntity(
            code: 'MARITAL_STATUS',
            company_uuid: $this->company->uuid,
        );
        $result = $this->repository->getValuesByType($typeEntity);

        $this->assertCount(2, $result->items);
        $this->assertEquals('MARRIED', $result->items[0]->code);
    }

    public function test_delete_value_performs_soft_delete()
    {
        $value = LookupValue::factory()->create(['company_uuid' => $this->company->uuid]);
        
        $this->repository->deleteValue($value->uuid);

        $deleted = LookupValue::withTrashed()->where('uuid', $value->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);
    }

    public function test_delete_type_soft_deletes_lookup_type()
    {
        $type = LookupType::factory()->create(['company_uuid' => $this->company->uuid]);

        $this->repository->deleteType($type->uuid);

        $this->assertSoftDeleted('lookup_types', ['uuid' => $type->uuid]);
    }

    public function test_get_type_by_code_returns_matching_type()
    {
        $type = LookupType::factory()->create([
            'code' => 'GENDER',
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->getTypeByCode(new LookupTypeEntity(
            code: 'GENDER',
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($type->uuid, $found->uuid);
    }

    public function test_get_value_by_uuid_returns_matching_value()
    {
        $value = LookupValue::factory()->create(['company_uuid' => $this->company->uuid]);

        $found = $this->repository->getValueByUuid(new LookupValueEntity(
            uuid: $value->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($value->uuid, $found->uuid);
    }

    public function test_get_value_by_code_returns_matching_value()
    {
        $type = LookupType::factory()->create(['company_uuid' => $this->company->uuid]);
        $value = LookupValue::factory()->create([
            'lookup_set_uuid' => $type->uuid,
            'code' => 'MALE',
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->getValueByCode(new LookupValueEntity(
            lookup_type_uuid: $type->uuid,
            code: 'MALE',
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($value->uuid, $found->uuid);
    }

    public function test_get_values_page_data_returns_paginated_values()
    {
        $type = LookupType::factory()->create(['company_uuid' => $this->company->uuid]);
        LookupValue::factory()->count(3)->create([
            'lookup_set_uuid' => $type->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $result = $this->repository->getValuesPageData(new LookupValueEntity(
            lookup_type_uuid: $type->uuid,
            page: 1,
            per_page: 2,
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }
}
