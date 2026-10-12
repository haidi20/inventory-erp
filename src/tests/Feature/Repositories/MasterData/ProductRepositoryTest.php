<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Repositories\ProductRepository;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_products()
    {
        Product::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new ProductEntity(page: 1, per_page: 5);
        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
    }

    public function test_get_page_data_only_returns_products_from_the_same_company()
    {
        Product::factory()->create([
            'code' => 'PRD-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Product::factory()->create([
            'code' => 'PRD-OTHER',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 10
        );

        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertSame('PRD-SBX', $result['items'][0]->code);
    }

    public function test_get_page_data_with_search()
    {
        Product::factory()->create([
            'code' => 'OBT-001',
            'name' => 'Paracetamol',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);
        Product::factory()->create([
            'code' => 'OBT-002',
            'name' => 'Amoxicillin',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new ProductEntity(search: 'para', page: 1, per_page: 10);
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('OBT-001', $result['items'][0]->code);
    }

    public function test_find_by_uuid_returns_product_with_stock()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $warehouse = Location::factory()->create(['branch_uuid' => $this->branch->uuid]);
        
        // stock initialization is automatic in save(), but factories might bypass it depending on how they are used.
        // Here we manually check FindByUUID metadata
        $searchEntity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );
        $found = $this->repository->findByUuid($searchEntity);

        $this->assertNotNull($found);
        $this->assertEquals($product->uuid, $found->uuid);
        $this->assertIsFloat($found->stock);
    }

    public function test_save_creates_new_product_and_initializes_stocks_for_all_warehouses()
    {
        $wh1 = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $wh2 = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $whOther = Location::factory()->create(); // In another branch

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'PROD-NEW',
            name: 'New Product',
            is_active: true
        );

        $product = $this->repository->save($entity);

        $this->assertDatabaseHas('items', [
            'uuid' => $product->uuid,
            'code' => 'PROD-NEW'
        ]);

        // Should have stock records for all warehouses in this branch (wh1, wh2)
        $this->assertDatabaseHas('item_stocks', [
            'item_uuid' => $product->uuid,
            'location_uuid' => $wh1->uuid,
            'on_hand_qty' => 0
        ]);
        $this->assertDatabaseHas('item_stocks', [
            'item_uuid' => $product->uuid,
            'location_uuid' => $wh2->uuid,
            'on_hand_qty' => 0
        ]);
        
        // Should NOT have stock record for other branch warehouse
        $this->assertDatabaseMissing('item_stocks', [
            'item_uuid' => $product->uuid,
            'location_uuid' => $whOther->uuid
        ]);
    }

    public function test_reduce_stock_creates_movement_and_updates_stock()
    {
        $warehouse = Location::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        // Initial stock (usually 0 from auto-init)
        ProductLocation::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_uuid' => $product->uuid,
            'location_uuid' => $warehouse->uuid,
            'on_hand_qty' => 100.0,
            'available_qty' => 100.0
        ]);

        $this->repository->reduceStock(
            $warehouse->uuid,
            $product->uuid,
            10,
            'SALE',
            (string) Str::uuid(),
            'actor-uuid'
        );

        $this->assertDatabaseHas('item_stocks', [
            'item_uuid' => $product->uuid,
            'location_uuid' => $warehouse->uuid,
            'on_hand_qty' => 90
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_uuid' => $product->uuid,
            'location_uuid' => $warehouse->uuid,
            'type' => 'OUT',
            'qty' => 10
        ]);
    }

    public function test_delete_performs_soft_delete()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $deleteEntity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );
        $this->repository->delete($deleteEntity);

        $this->assertSoftDeleted('items', ['uuid' => $product->uuid]);
    }

    public function test_list_for_export_only_returns_products_from_the_same_company()
    {
        Product::factory()->create([
            'code' => 'EXP-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Product::factory()->create([
            'code' => 'EXP-OTHER',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $items = $this->repository->listForExport(new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $items);
        $this->assertSame('EXP-SBX', $items[0]->code);
    }

    public function test_get_page_data_applies_all_product_filters(): void
    {
        $fixtures = $this->createProductFilterFixtures();

        $this->assertFilteredProducts(['filter_barcode' => 'E2E-BARCODE-A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_barcode' => 'E2E-BARCODE-B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_code' => 'FILTER-A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_code' => 'FILTER-B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_name' => 'E2E Filter Name A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_name' => 'E2E Filter Name B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_unit' => 'E2E Unit A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_unit' => 'E2E Unit B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_group_uuid' => $fixtures['group_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_group_uuid' => $fixtures['group_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_keterangan' => 'E2E Description A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_keterangan' => 'E2E Description B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_kind' => 'E2E Kind A'], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_kind' => 'E2E Kind B'], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_type_uuid' => $fixtures['type_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_type_uuid' => $fixtures['type_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_brand_uuid' => $fixtures['brand_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_brand_uuid' => $fixtures['brand_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_origin_uuid' => $fixtures['origin_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_origin_uuid' => $fixtures['origin_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_grade_uuid' => $fixtures['grade_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_grade_uuid' => $fixtures['grade_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_created_by' => $fixtures['user_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_created_by' => $fixtures['user_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_updated_by' => $fixtures['user_a']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_updated_by' => $fixtures['user_b']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_created_from' => $fixtures['current_date']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_created_to' => $fixtures['current_date']], ['FILTER-A', 'FILTER-B']);
        $this->assertFilteredProducts(['filter_created_from' => $fixtures['previous_date']], ['FILTER-A', 'FILTER-B']);
        $this->assertFilteredProducts(['filter_created_to' => $fixtures['previous_date']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_updated_from' => $fixtures['current_date']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_updated_to' => $fixtures['current_date']], ['FILTER-A', 'FILTER-B']);
        $this->assertFilteredProducts(['filter_updated_from' => $fixtures['previous_date']], ['FILTER-A', 'FILTER-B']);
        $this->assertFilteredProducts(['filter_updated_to' => $fixtures['previous_date']], ['FILTER-B']);
        $this->assertFilteredProducts(['filter_periode' => $fixtures['current_period']], ['FILTER-A']);
        $this->assertFilteredProducts(['filter_periode' => $fixtures['previous_period']], ['FILTER-B']);
    }

    public function test_get_page_data_filters_by_company_for_superadmin(): void
    {
        $product = Product::factory()->create([
            'code' => 'FILTER-A',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();

        $this->assertFilteredProducts([
            'is_superadmin' => true,
            'filter_company_uuid' => $this->company->uuid,
        ], ['FILTER-A']);

        $this->assertFilteredProducts([
            'is_superadmin' => true,
            'filter_company_uuid' => $otherCompany->uuid,
        ], []);

        $this->assertNotNull($product->uuid);
    }

    private function createProductFilterFixtures(): array
    {
        $userA = User::factory()->create(['name' => 'E2E Product User A']);
        $userB = User::factory()->create(['name' => 'E2E Product User B']);
        $unitA = Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'E2E-UNIT-A',
            'name' => 'E2E Unit A',
        ]);
        $unitB = Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'E2E-UNIT-B',
            'name' => 'E2E Unit B',
        ]);

        $lookupDefinitions = [
            'KELOMPOK' => 'group',
            'TIPE' => 'type',
            'MERK' => 'brand',
            'MADE_IN' => 'origin',
            'GRADE' => 'grade',
        ];
        $lookups = [];

        foreach ($lookupDefinitions as $setCode => $key) {
            $set = LookupSet::factory()->create([
                'code' => $setCode,
                'name' => 'E2E Product ' . ucfirst($key),
                'program_code' => $setCode,
                'company_uuid' => $this->company->uuid,
            ]);

            foreach (['A', 'B'] as $suffix) {
                $value = LookupValue::factory()->create([
                    'lookup_set_uuid' => $set->uuid,
                    'code' => $setCode . '-' . $suffix,
                    'display_name' => 'E2E Filter ' . ucfirst($key) . ' ' . $suffix,
                    'company_uuid' => $this->company->uuid,
                ]);
                $lookups[$key . '_' . strtolower($suffix)] = $value->uuid;
            }
        }

        $currentDate = now()->startOfMonth()->addDay();
        $previousDate = now()->subMonthNoOverflow()->startOfMonth()->addDay();

        Product::factory()->create([
            'code' => 'FILTER-A',
            'name' => 'E2E Filter Name A',
            'barcode' => 'E2E-BARCODE-A',
            'description' => 'E2E Description A',
            'base_unit_uuid' => $unitA->uuid,
            'unit' => $unitA->name,
            'kind' => 'E2E Kind A',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_group_lookup_uuid' => $lookups['group_a'],
            'item_type_lookup_uuid' => $lookups['type_a'],
            'brand_lookup_uuid' => $lookups['brand_a'],
            'origin_lookup_uuid' => $lookups['origin_a'],
            'grade_lookup_uuid' => $lookups['grade_a'],
            'created_by' => $userA->uuid,
            'updated_by' => $userA->uuid,
            'created_at' => $currentDate->timestamp,
            'updated_at' => $currentDate->timestamp,
        ]);

        Product::factory()->create([
            'code' => 'FILTER-B',
            'name' => 'E2E Filter Name B',
            'barcode' => 'E2E-BARCODE-B',
            'description' => 'E2E Description B',
            'base_unit_uuid' => $unitB->uuid,
            'unit' => $unitB->name,
            'kind' => 'E2E Kind B',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_group_lookup_uuid' => $lookups['group_b'],
            'item_type_lookup_uuid' => $lookups['type_b'],
            'brand_lookup_uuid' => $lookups['brand_b'],
            'origin_lookup_uuid' => $lookups['origin_b'],
            'grade_lookup_uuid' => $lookups['grade_b'],
            'created_by' => $userB->uuid,
            'updated_by' => $userB->uuid,
            'created_at' => $previousDate->timestamp,
            'updated_at' => $previousDate->timestamp,
        ]);

        return [
            ...$lookups,
            'user_a' => $userA->uuid,
            'user_b' => $userB->uuid,
            'current_date' => $currentDate->format('Y-m-d'),
            'previous_date' => $previousDate->format('Y-m-d'),
            'current_period' => $currentDate->format('Y-m'),
            'previous_period' => $previousDate->format('Y-m'),
        ];
    }

    private function assertFilteredProducts(array $filters, array $expectedCodes): void
    {
        $entity = new ProductEntity(...array_merge($filters, [
            'branch_uuid' => $filters['is_superadmin'] ?? false ? null : $this->branch->uuid,
            'company_uuid' => $filters['is_superadmin'] ?? false ? null : $this->company->uuid,
            'page' => 1,
            'per_page' => 10,
        ]));

        $result = $this->repository->getPageData($entity);
        $codes = collect($result['items']->items())->pluck('code')->all();

        $this->assertSame($expectedCodes, $codes, json_encode($filters));
    }
}
