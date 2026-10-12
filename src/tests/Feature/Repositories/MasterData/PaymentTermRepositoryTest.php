<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PaymentTerm;
use App\Modules\Operational\Master\Repositories\PaymentTermRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentTermRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PaymentTermRepository $repository;
    private Company $company;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PaymentTermRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_terms()
    {
        PaymentTerm::factory()->count(10)->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);

        $entity = new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 5
        );
        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
    }

    public function test_get_page_data_with_search()
    {
        PaymentTerm::factory()->create([
            'name' => 'Cash On Delivery',
            'description' => 'COD',
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        PaymentTerm::factory()->create([
            'name' => 'Net 30',
            'description' => 'N30',
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);

        $entity = new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            search: 'cash',
            page: 1,
            per_page: 10
        );
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Cash On Delivery', $result['items'][0]->name);
    }

    public function test_get_page_data_only_returns_own_company_terms()
    {
        PaymentTerm::factory()->create([
            'name' => 'Term Perusahaan A',
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        PaymentTerm::factory()->create([
            'name' => 'Term Perusahaan Lain',
            'company_uuid' => $otherCompany->uuid,
            'branch_uuid' => $otherBranch->uuid,
        ]);

        $entity = new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 10
        );
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Term Perusahaan A', $result['items'][0]->name);
    }

    public function test_find_by_uuid_returns_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);

        $found = $this->repository->findByUuid(new PaymentTermEntity(
            uuid: $term->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertEquals($term->name, $found->name);
    }

    public function test_find_by_uuid_returns_null_for_different_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $term = PaymentTerm::factory()->create([
            'company_uuid' => $otherCompany->uuid,
            'branch_uuid' => $otherBranch->uuid,
        ]);

        $found = $this->repository->findByUuid(new PaymentTermEntity(
            uuid: $term->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNull($found);
    }

    public function test_save_creates_new_term()
    {
        $entity = new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            branch_uuid: $this->branch->uuid,
            name: 'Net 15',
            due_days: 15,
            is_active: true,
            is_primary: false
        );

        $term = $this->repository->save($entity);

        $this->assertDatabaseHas('payment_terms', [
            'uuid' => $term->uuid,
            'name' => 'Net 15',
            'due_days' => 15,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_delete_removes_term()
    {
        $term = PaymentTerm::factory()->create([
            'company_uuid' => $this->company->uuid,
        ]);
        $entity = new PaymentTermEntity(uuid: $term->uuid);

        $this->repository->delete($entity);

        $this->assertDatabaseMissing('payment_terms', ['uuid' => $term->uuid, 'deleted_at' => 0]);
    }

    public function test_list_for_export_only_returns_own_company_terms()
    {
        PaymentTerm::factory()->count(2)->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $otherCompany = Company::factory()->create();
        PaymentTerm::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $entity = new PaymentTermEntity(company_uuid: $this->company->uuid);
        $list = $this->repository->listForExport($entity);

        $this->assertIsArray($list);
        $this->assertCount(2, $list);
    }
}
