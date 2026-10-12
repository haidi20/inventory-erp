<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use App\Modules\Operational\Master\Models\LookupType;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Repositories\LookupRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupRepository;
use App\Modules\Operational\Master\Services\LookupService;
use Mockery;
use Tests\TestCase;

class LookupServiceTest extends TestCase
{
    private $repositoryMock;
    private LookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(LookupRepositoryInterface::class);
        $this->service = new LookupService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_values_validates_type_code()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getValues(LookupTypeEntity::fromArray(['code' => '']));
    }

    public function test_store_type_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->storeType(LookupTypeEntity::fromArray(['code' => '']));
    }

    public function test_store_value_calls_repository()
    {
        $input = LookupValueEntity::fromArray([
            'lookup_type_uuid' => 'type-uuid',
            'lookup_type_uuid' => 'type-uuid',
            'code' => 'CODE1',
            'value' => 'Label 1'
        ]);

        $this->repositoryMock->shouldReceive('getValueByCode')->once()->andReturn(null);
        $this->repositoryMock->shouldReceive('saveValue')->once()->andReturn(
            LookupValueEntity::fromArray([
                'uuid' => 'value-uuid',
                'lookup_type_uuid' => 'type-uuid',
                'code' => 'CODE1',
                'value' => 'Label 1',
            ])
        );

        $result = $this->service->storeValue($input);
        $this->assertNotNull($result);
        $this->assertSame('CODE1', $result->code);
    }

    public function test_get_types_returns_all_lookup_types()
    {
        LookupType::factory()->count(2)->create();

        $service = new LookupService(new LookupRepository());
        $result = $service->getTypes();

        $this->assertCount(2, $result->items);
    }

    public function test_get_type_by_code_returns_matching_type()
    {
        $type = LookupType::factory()->create(['code' => 'GENDER']);

        $service = new LookupService(new LookupRepository());
        $found = $service->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => 'GENDER',
            'company_uuid' => $type->company_uuid,
        ]));

        $this->assertNotNull($found);
        $this->assertSame($type->uuid, $found->uuid);
    }

    public function test_update_value_updates_existing_lookup_value()
    {
        $type = LookupType::factory()->create();
        $value = LookupValue::factory()->create([
            'lookup_set_uuid' => $type->uuid,
            'code' => 'MALE',
            'value' => 'Laki-laki',
        ]);

        $service = new LookupService(new LookupRepository());
        $updated = $service->updateValue(LookupValueEntity::fromArray([
            'uuid' => $value->uuid,
            'value' => 'Pria',
            'description' => 'Jenis kelamin pria',
        ]));

        $this->assertSame('Pria', $updated->value);
        $this->assertDatabaseHas('lookup_values', [
            'uuid' => $value->uuid,
            'value' => 'Pria',
        ]);
    }

    public function test_delete_type_soft_deletes_lookup_type()
    {
        $type = LookupType::factory()->create();

        $service = new LookupService(new LookupRepository());
        $service->deleteType($type->uuid);

        $this->assertSoftDeleted('lookup_types', ['uuid' => $type->uuid]);
    }

    public function test_delete_value_soft_deletes_lookup_value()
    {
        $value = LookupValue::factory()->create();

        $service = new LookupService(new LookupRepository());
        $service->deleteValue(LookupValueEntity::fromArray(['uuid' => $value->uuid]));

        $this->assertSoftDeleted('lookup_values', ['uuid' => $value->uuid]);
    }

    public function test_get_types_page_returns_paginated_types()
    {
        LookupType::factory()->count(3)->create(['company_uuid' => 'company-lookup']);

        $service = new LookupService(new LookupRepository());
        $result = $service->getTypesPage(LookupTypeEntity::fromArray([
            'page' => 1,
            'per_page' => 2,
            'company_uuid' => 'company-lookup',
        ]));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_get_values_page_returns_paginated_values()
    {
        $type = LookupType::factory()->create();
        LookupValue::factory()->count(3)->create(['lookup_set_uuid' => $type->uuid]);

        $service = new LookupService(new LookupRepository());
        $result = $service->getValuesPage(LookupValueEntity::fromArray([
            'lookup_type_uuid' => $type->uuid,
            'page' => 1,
            'per_page' => 2,
        ]));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_export_values_returns_document_with_values()
    {
        $set = LookupSet::factory()->create(['code' => 'GENDER']);
        LookupValue::factory()->create(['lookup_set_uuid' => $set->uuid]);

        $service = new LookupService(new LookupRepository());
        $document = $service->exportValues(LookupTypeEntity::fromArray([
            'code' => 'GENDER',
            'company_uuid' => $set->company_uuid,
        ]));

        $this->assertSame('Data Lookup', $document->sheet);
        $this->assertCount(1, $document->items);
    }

    public function test_import_values_creates_lookup_values()
    {
        $type = LookupType::factory()->create(['code' => 'GENDER']);

        $service = new LookupService(new LookupRepository());
        $service->importValues(new \App\Modules\Operational\Master\Entities\LookupImportEntity(
            type_code: 'GENDER',
            company_uuid: $type->company_uuid,
            rows: [
                ['Code', 'Label'],
                ['MALE', 'Laki-laki'],
            ],
        ));

        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $type->uuid,
            'code' => 'MALE',
            'value' => 'Laki-laki',
        ]);
    }

    public function test_get_import_template_returns_lookup_structure()
    {
        $service = new LookupService(new LookupRepository());
        $document = $service->getImportTemplate();

        $this->assertSame('Data Lookup', $document->sheet);
        $this->assertContains('Kode', $document->headers);
    }
}
