<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\ItemConversion;
use App\Modules\Operational\Master\Models\ItemUom;
use App\Modules\Operational\Master\Repositories\ItemConversionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ItemConversionRepositoryTest
 * 
 * Pengujian untuk manajemen konversi satuan barang.
 * Diselaraskan dengan skema database aktual (uom_code, to_qty, from_unit_uuid).
 */
class ItemConversionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ItemConversionRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ItemConversionRepository();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    /**
     * Test Get Page Data
     */
    public function test_get_page_data_returns_paginated_conversions()
    {
        ItemConversion::withoutEvents(function (): void {
            ItemConversion::factory()->count(5)->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
                'item_uuid' => (string) Str::uuid()
            ]);

            ItemConversion::factory()->count(2)->create([
                'branch_uuid' => (string) Str::uuid(),
                'company_uuid' => (string) Str::uuid(),
                'item_uuid' => (string) Str::uuid()
            ]);
        });

        $entity = new ItemConversionEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(5, $result['total']);
    }

    /**
     * Test Save (Create) conversion
     */
    public function test_save_creates_new_conversion()
    {
        $entity = new ItemConversionEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            item_uuid: (string) Str::uuid(),
            from_unit_uuid: (string) Str::uuid(),
            to_unit_uuid: (string) Str::uuid(),
            conv_ratio: 12.0,
            is_active: true
        );

        $conv = $this->repository->save($entity);

        $this->assertDatabaseHas('item_conversions', [
            'uuid' => $conv->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'conversion_ratio' => 12.0
        ]);
    }

    public function test_get_by_units_is_scoped_by_branch_and_company(): void
    {
        $fromUnit = (string) Str::uuid();
        $toUnit = (string) Str::uuid();
        $itemUuid = (string) Str::uuid();

        $own = ItemConversion::withoutEvents(function () use ($itemUuid, $fromUnit, $toUnit) {
            return ItemConversion::factory()->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
                'item_uuid' => $itemUuid,
                'from_unit_uuid' => $fromUnit,
                'to_unit_uuid' => $toUnit,
            ]);
        });

        ItemConversion::withoutEvents(function () use ($itemUuid, $fromUnit, $toUnit): void {
            ItemConversion::factory()->create([
                'branch_uuid' => (string) Str::uuid(),
                'company_uuid' => (string) Str::uuid(),
                'item_uuid' => $itemUuid,
                'from_unit_uuid' => $fromUnit,
                'to_unit_uuid' => $toUnit,
            ]);
        });

        $found = $this->repository->getByUnits($fromUnit, $toUnit, $this->branch->uuid, $this->company->uuid, $itemUuid);

        $this->assertNotNull($found);
        $this->assertSame($own->uuid, $found->uuid);
    }

    /**
     * Test Save UOM (Legacy/Direct)
     */
    public function test_save_uom_creates_item_uom()
    {
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_uuid' => (string) Str::uuid(),
            'uom_code' => 'STRIP',
            'to_qty' => 10,
            'barcode' => '123456789'
        ];

        $uom = $this->repository->saveUom($data);

        $this->assertDatabaseHas('item_uoms', [
            'uuid' => $uom->uuid,
            'uom_code' => 'STRIP',
            'to_qty' => 10
        ]);
    }
}
