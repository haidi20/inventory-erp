<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Master\Repositories\SupplierRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SupplierRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SupplierRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_suppliers()
    {
        Supplier::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_active' => true
        ]);

        $entity = new SupplierEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['activeSuppliers']);
        $this->assertInstanceOf(SupplierEntity::class, $result['items'][0]);
    }

    public function test_get_page_data_with_search()
    {
        Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'PT Sumber Medika',
            'phone' => '08123456789'
        ]);

        Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Apotek Jaya',
            'phone' => '08987654321'
        ]);

        // Search by name
        $entity = new SupplierEntity(branch_uuid: $this->branch->uuid, company_uuid: $this->company->uuid, search: 'Sumber');
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('PT Sumber Medika', $result['items'][0]->name);

        // Search by phone
        $entity->search = '0898765';
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Apotek Jaya', $result['items'][0]->name);
    }

    public function test_save_creates_new_supplier()
    {
        $entity = new SupplierEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            name: 'Pemasok Alkes Utama',
            phone: '021123456',
            email: 'alkes@example.com',
            address: 'Jl. Medika No. 10',
            term_days: 30,
            is_active: true
        );

        $saved = $this->repository->save($entity);

        $this->assertDatabaseHas('suppliers', [
            'uuid' => $saved->uuid,
            'name' => 'Pemasok Alkes Utama'
        ]);
        $this->assertEquals(30, $saved->term_days);
    }

    public function test_save_updates_existing_supplier()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Nama Lama'
        ]);

        $entity = SupplierEntity::fromModel($supplier);
        $entity->name = 'Nama Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Nama Baru', $updated->name);
        $this->assertDatabaseHas('suppliers', [
            'uuid' => $supplier->uuid,
            'name' => 'Nama Baru'
        ]);
    }

    public function test_delete_performs_soft_delete()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $entity = SupplierEntity::fromModel($supplier);

        $this->repository->delete($entity);

        $deleted = Supplier::withTrashed()->where('uuid', $supplier->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);
    }

    public function test_find_by_uuid_returns_correct_supplier()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->findByUuid(new SupplierEntity(
            uuid: $supplier->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertEquals($supplier->uuid, $found->uuid);
    }

    public function test_get_page_data_only_returns_suppliers_from_the_same_company()
    {
        Supplier::factory()->create([
            'name' => 'Supplier Sandbox',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Supplier::factory()->create([
            'name' => 'Supplier Other',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $result = $this->repository->getPageData(new SupplierEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 10,
            page: 1,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame('Supplier Sandbox', $result['items'][0]->name);
        $this->assertSame(1, $result['activeSuppliers']);
    }

    public function test_has_purchases_reflects_purchase_presence()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->assertFalse($this->repository->hasPurchases(new SupplierEntity(
            uuid: $supplier->uuid,
            branch_uuid: $supplier->branch_uuid,
            company_uuid: $supplier->company_uuid,
        )));

        Purchase::create([
            'branch_uuid' => $supplier->branch_uuid,
            'company_uuid' => $supplier->company_uuid,
            'invoice_no' => 'INV-TEST-001',
            'supplier_uuid' => $supplier->uuid,
            'purchase_date' => now()->timestamp,
        ]);

        $this->assertTrue($this->repository->hasPurchases(new SupplierEntity(
            uuid: $supplier->uuid,
            branch_uuid: $supplier->branch_uuid,
            company_uuid: $supplier->company_uuid,
        )));
    }
}
