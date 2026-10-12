<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\LookupImportEntity;
use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueListResultEntity;
use App\Modules\Operational\Master\Repositories\LookupSetRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupSetRepository;
use App\Modules\Operational\Master\Services\LookupSetService;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\TempLookup;
use Mockery;
use Tests\TestCase;

class LookupSetServiceTest extends TestCase
{
    private $repositoryMock;
    private LookupSetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(LookupSetRepositoryInterface::class);
        $this->service = new LookupSetService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_set_validates_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->storeSet(LookupSetEntity::fromArray(['name' => '']));
    }

    public function test_store_value_validates_category()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->storeValue(LookupSetValueEntity::fromArray([
            'display_name' => 'Test',
            'lookup_set_uuid' => 0,
        ]));
    }

    public function test_get_values_by_set_name_calls_repository()
    {
        $this->repositoryMock->shouldReceive('getValuesBySetName')
            ->once()
            ->andReturn(new LookupSetValueListResultEntity([], 0, 0));

        $result = $this->service->getValuesBySetName(LookupSetEntity::fromArray(['name' => 'TEST_CAT']));
        $this->assertInstanceOf(LookupSetValueListResultEntity::class, $result);
    }

    public function test_import_value_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->importValue(new LookupImportEntity(lookup_set_uuid: 0, rows: []));
    }

    public function test_index_set_returns_paginated_sets()
    {
        LookupSet::factory()->count(3)->create(['company_uuid' => 'company-lookup-set']);

        $service = new LookupSetService(new LookupSetRepository());
        $result = $service->indexSet(LookupSetEntity::fromArray([
            'page' => 1,
            'per_page' => 2,
            'company_uuid' => 'company-lookup-set',
        ]));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_show_set_returns_matching_set()
    {
        $set = LookupSet::factory()->create();

        $service = new LookupSetService(new LookupSetRepository());
        $found = $service->showSet(LookupSetEntity::fromArray(['uuid' => $set->uuid]));

        $this->assertNotNull($found);
        $this->assertSame($set->uuid, $found->uuid);
    }

    public function test_update_set_updates_existing_set()
    {
        $set = LookupSet::factory()->create(['name' => 'Lama']);

        $service = new LookupSetService(new LookupSetRepository());
        $updated = $service->updateSet(LookupSetEntity::fromArray([
            'uuid' => $set->uuid,
            'name' => 'Baru',
            'program_code' => 'PC-UPDATE',
        ]));

        $this->assertSame('Baru', $updated->name);
        $this->assertDatabaseHas('lookup_sets', [
            'uuid' => $set->uuid,
            'name' => 'Baru',
        ]);
    }

    public function test_destroy_set_soft_deletes_set()
    {
        $set = LookupSet::factory()->create();

        $service = new LookupSetService(new LookupSetRepository());
        $service->destroySet($set->uuid);

        $this->assertSoftDeleted('lookup_sets', ['uuid' => $set->uuid]);
    }

    public function test_index_value_returns_paginated_values()
    {
        $set = LookupSet::factory()->create();
        LookupValue::factory()->count(3)->create(['lookup_set_uuid' => $set->uuid]);

        $service = new LookupSetService(new LookupSetRepository());
        $result = $service->indexValue(LookupSetValueEntity::fromArray([
            'lookup_set_uuid' => $set->uuid,
            'start' => 0,
            'length' => 2,
        ]));

        $this->assertCount(2, $result->items);
        $this->assertSame(3, $result->total);
    }

    public function test_show_value_returns_matching_value()
    {
        $value = LookupValue::factory()->create();

        $service = new LookupSetService(new LookupSetRepository());
        $found = $service->showValue(LookupSetValueEntity::fromArray(['uuid' => $value->uuid]));

        $this->assertNotNull($found);
        $this->assertSame($value->uuid, $found->uuid);
    }

    public function test_update_value_updates_existing_value()
    {
        $value = LookupValue::factory()->create(['display_name' => 'Lama']);

        $service = new LookupSetService(new LookupSetRepository());
        $updated = $service->updateValue(LookupSetValueEntity::fromArray([
            'uuid' => $value->uuid,
            'display_name' => 'Baru',
        ]));

        $this->assertSame('Baru', $updated->display_name);
        $this->assertDatabaseHas('lookup_values', [
            'uuid' => $value->uuid,
            'display_name' => 'Baru',
        ]);
    }

    public function test_destroy_value_soft_deletes_value()
    {
        $value = LookupValue::factory()->create();

        $service = new LookupSetService(new LookupSetRepository());
        $service->destroyValue($value->uuid);

        $this->assertSoftDeleted('lookup_values', ['uuid' => $value->uuid]);
    }

    public function test_get_import_template_returns_lookup_set_structure()
    {
        $service = new LookupSetService(new LookupSetRepository());
        $document = $service->getImportTemplate();

        $this->assertSame('Daftar Nilai', $document->sheet);
        $this->assertContains('DISPLAY_NAME', $document->headers);
    }

    public function test_export_set_returns_document_with_sets()
    {
        LookupSet::factory()->count(2)->create();

        $service = new LookupSetService(new LookupSetRepository());
        $document = $service->exportSet(LookupSetEntity::fromArray([
            'page' => 1,
            'per_page' => 10,
        ]));

        $this->assertSame('Kategori Lookup', $document->sheet);
        $this->assertCount(2, $document->items);
    }

    public function test_get_set_import_template_returns_set_structure()
    {
        $service = new LookupSetService(new LookupSetRepository());
        $document = $service->getSetImportTemplate();

        $this->assertSame('Lookup Set', $document->sheet);
        $this->assertContains('NAME', $document->headers);
    }

    public function test_import_set_creates_lookup_sets()
    {
        $service = new LookupSetService(new LookupSetRepository());
        $service->importSet(new LookupImportEntity(
            company_uuid: 'company-lookup-set',
            rows: [
                ['PROGRAM_CODE', 'NAME'],
                ['PC-IMPORT', 'Kategori Import'],
            ],
        ));

        $this->assertDatabaseHas('lookup_sets', [
            'program_code' => 'PC-IMPORT',
            'name' => 'Kategori Import',
        ]);
    }

    public function test_export_value_returns_document_with_values()
    {
        $set = LookupSet::factory()->create();
        LookupValue::factory()->count(2)->create(['lookup_set_uuid' => $set->uuid]);

        $service = new LookupSetService(new LookupSetRepository());
        $document = $service->exportValue(LookupSetValueEntity::fromArray([
            'lookup_set_uuid' => $set->uuid,
            'page' => 1,
            'per_page' => 10,
        ]));

        $this->assertSame('Data Nilai', $document->sheet);
        $this->assertCount(2, $document->items);
    }

    public function test_sync_value_moves_temp_values_to_master()
    {
        $set = LookupSet::factory()->create();
        TempLookup::create([
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
            'seq' => 1,
            'active_flag' => 'Y',
        ]);

        $service = new LookupSetService(new LookupSetRepository());
        $service->syncValue(LookupSetEntity::fromArray(['uuid' => $set->uuid]));

        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
        ]);
        $this->assertDatabaseMissing('temp_lovs', [
            'lookup_set_uuid' => $set->uuid,
            'display_name' => 'Temp Value',
        ]);
    }
}
