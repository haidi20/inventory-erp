<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Tax;
use App\Modules\Operational\Master\Repositories\TaxRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TaxRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TaxRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_taxes()
    {
        Tax::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_active' => true
        ]);

        $entity = new TaxEntity(
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['active_taxes']);
        $this->assertInstanceOf(TaxEntity::class, $result['items'][0]);
    }

    public function test_get_page_data_only_returns_own_company_taxes()
    {
        Tax::factory()->create([
            'tax_code' => 'PPN_MINE',
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Tax::factory()->create([
            'tax_code' => 'PPN_OTHER',
            'company_uuid' => $otherCompany->uuid,
            'branch_uuid' => $otherBranch->uuid,
        ]);

        $entity = new TaxEntity(company_uuid: $this->company->uuid, page: 1, per_page: 10);
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('PPN_MINE', $result['items'][0]->tax_code);
    }

    public function test_save_creates_new_tax_and_supports_soft_delete_reentry()
    {
        $code = 'PPN12';

        // 1. Create and save new tax
        $entity = new TaxEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            tax_code: $code,
            percentage: 12.0,
            is_active: true
        );

        $saved = $this->repository->save($entity);
        $this->assertDatabaseHas('taxes', ['uuid' => $saved->uuid, 'tax_code' => $code]);

        // 2. Soft delete the tax
        $this->repository->delete($saved);
        
        $deleted = Tax::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // 3. Create NEW tax with SAME code (should work)
        $newEntity = new TaxEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            tax_code: $code,
            percentage: 12.0,
            is_primary: true
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
    }

    public function test_save_updates_existing_tax()
    {
        $tax = Tax::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'tax_code' => 'OLDTAX',
            'percentage' => 5.0
        ]);

        $entity = TaxEntity::fromModel($tax);
        $entity->percentage = 7.5;

        $updated = $this->repository->save($entity);

        $this->assertEquals(7.5, $updated->percentage);
        $this->assertDatabaseHas('taxes', [
            'uuid' => $tax->uuid,
            'percentage' => 7.5
        ]);
    }

    public function test_find_by_uuid_returns_correct_tax()
    {
        $tax = Tax::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $found = $this->repository->findByUuid(new TaxEntity(
            uuid: $tax->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertEquals($tax->uuid, $found->uuid);
    }

    public function test_find_by_uuid_returns_null_for_different_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $tax = Tax::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $found = $this->repository->findByUuid(new TaxEntity(
            uuid: $tax->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNull($found);
    }

    public function test_list_for_export_only_returns_own_company_taxes()
    {
        Tax::factory()->count(2)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $otherCompany = Company::factory()->create();
        Tax::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $result = $this->repository->listForExport(new TaxEntity(
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(TaxEntity::class, $result);
    }
}
