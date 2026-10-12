<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\Service;
use Tests\TestCase;
use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Operational\Master\Entities\TreatmentPackageItemEntity;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepository;

class TreatmentPackageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TreatmentPackageRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TreatmentPackageRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_packages()
    {
        ServicePackage::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_active' => true
        ]);

        $entity = new TreatmentPackageEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['activePackages']);
        $this->assertInstanceOf(TreatmentPackageEntity::class, $result['items'][0]);
    }

    public function test_save_creates_package_with_items()
    {
        $service = Service::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        $product = Product::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);

        $items = [
            new TreatmentPackageItemEntity(
                item_type: 'service',
                item_uuid: $service->uuid,
                qty: 1,
                branch_uuid: $this->branch->uuid,
                company_uuid: $this->company->uuid
            ),
            new TreatmentPackageItemEntity(
                item_type: 'product',
                item_uuid: $product->uuid,
                qty: 2,
                branch_uuid: $this->branch->uuid,
                company_uuid: $this->company->uuid
            )
        ];

        $entity = new TreatmentPackageEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'PKG-MED-01',
            name: 'Paket Medis Standar',
            price: 250000,
            is_active: true,
            items: $items
        );

        $saved = $this->repository->save($entity);

        $this->assertDatabaseHas('service_packages', ['uuid' => $saved->uuid, 'code' => 'PKG-MED-01']);
        $this->assertDatabaseHas('service_package_items', [
            'service_package_uuid' => $saved->uuid,
            'item_type' => 'service',
            'item_uuid' => $service->uuid,
            'company_uuid' => $this->company->uuid
        ]);
        $this->assertDatabaseHas('service_package_items', [
            'service_package_uuid' => $saved->uuid,
            'item_type' => 'product',
            'item_uuid' => $product->uuid,
            'qty' => 2,
            'company_uuid' => $this->company->uuid
        ]);
    }

    public function test_save_updates_package_and_syncs_items()
    {
        $package = ServicePackage::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        $oldService = Service::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        
        $package->items()->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_type' => 'service',
            'item_uuid' => $oldService->uuid,
            'qty' => 1
        ]);

        $newService = Service::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        $items = [
            new TreatmentPackageItemEntity(
                item_type: 'service',
                item_uuid: $newService->uuid,
                qty: 5,
                branch_uuid: $this->branch->uuid,
                company_uuid: $this->company->uuid
            )
        ];

        $entity = TreatmentPackageEntity::fromModel($package->fresh(['items']));
        $entity->name = 'Updated Package Name';
        $entity->items = $items;

        $updated = $this->repository->save($entity);

        $this->assertEquals('Updated Package Name', $updated->name);
        $this->assertCount(1, $updated->items);
        $this->assertEquals($newService->uuid, $updated->items[0]->item_uuid);
        $this->assertEquals(5, $updated->items[0]->qty);
        
        // Ensure old item is soft deleted (deleted_at > 0)
        $this->assertDatabaseHas('service_package_items', [
            'service_package_uuid' => $package->uuid,
            'item_uuid' => $oldService->uuid,
        ]);
        
        $oldItem = ServicePackageItem::withTrashed()
            ->where('service_package_uuid', $package->uuid)
            ->where('item_uuid', $oldService->uuid)
            ->first();
        $this->assertNotEquals(0, $oldItem->deleted_at);
    }

    public function test_delete_performs_soft_delete_on_package_and_items()
    {
        $package = ServicePackage::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        $service = Service::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->company->uuid]);
        
        $item = $package->items()->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_type' => 'service',
            'item_uuid' => $service->uuid,
            'qty' => 1
        ]);

        $entity = TreatmentPackageEntity::fromModel($package->fresh(['items']));
        $this->repository->delete($entity);

        $deletedPackage = ServicePackage::withTrashed()->where('uuid', $package->uuid)->first();
        $this->assertNotNull($deletedPackage->deleted_at);

        $deletedItem = ServicePackageItem::withTrashed()->where('uuid', $item->uuid)->first();
        $this->assertNotNull($deletedItem->deleted_at);
    }

    public function test_get_page_data_only_returns_packages_from_the_same_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        ServicePackage::factory()->create([
            'code' => 'PKG-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        ServicePackage::factory()->create([
            'code' => 'PKG-OTH',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $result = $this->repository->getPageData(new TreatmentPackageEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame('PKG-SBX', $result['items'][0]->code);
    }
}
