<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Manufacturing\Models\Bom;
use App\Modules\Operational\Manufacturing\Repositories\BomRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Tests\TestCase;

class BomRepositoryTest extends TestCase
{
    use RefreshDatabase;

    use RefreshDatabase, InteractsWithSandboxRuntime;

    private BomRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BomRepository();
    }

    /** Create a second company + branch for isolation tests. */
    private function createCompanyB(): array
    {
        $company = Company::create([
            'uuid'      => (string) Str::uuid(),
            'name'      => 'Company B',
            'is_active' => true,
        ]);
        $branch = Branch::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $company->uuid,
            'short_name'   => 'B-B1',
            'name'         => 'Branch B1',
            'is_active'    => true,
        ]);
        return [$company, $branch];
    }

    // ---------------------------------------------------------------
    // getPageData isolation
    // ---------------------------------------------------------------

    public function test_get_page_data_returns_boms_of_own_company_only(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        [$companyB, $branchB] = $this->createCompanyB();

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'BOM-A-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid'  => $branchB->uuid,
            'document_no'  => 'BOM-B-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $entity = new BomEntity(company_uuid: $companyA->uuid, page: 1, per_page: 15);
        $result = $this->repository->getPageData($entity);

        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);
        /** @var BomEntity $first */
        $first = $result['items'][0];
        $this->assertEquals($companyA->uuid, $first->company_uuid);
    }

    public function test_get_page_data_does_not_return_other_company_boms(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        [$companyB, $branchB] = $this->createCompanyB();

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'BOM-A-002',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $entity = new BomEntity(company_uuid: $companyB->uuid, page: 1, per_page: 15);
        $result = $this->repository->getPageData($entity);

        $this->assertCount(0, $result['items']);
        $this->assertEquals(0, $result['total']);
    }

    // ---------------------------------------------------------------
    // listForExport isolation
    // ---------------------------------------------------------------

    public function test_list_for_export_scoped_by_company(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        [$companyB, $branchB] = $this->createCompanyB();

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'BOM-EXPORT-A',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid'  => $branchB->uuid,
            'document_no'  => 'BOM-EXPORT-B',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $entity = new BomEntity(company_uuid: $companyA->uuid);
        $result = $this->repository->listForExport($entity);

        $this->assertCount(1, $result);
        /** @var BomEntity $first */
        $first = $result[0];
        $this->assertEquals($companyA->uuid, $first->company_uuid);
    }

    // ---------------------------------------------------------------
    // save
    // ---------------------------------------------------------------

    public function test_save_creates_bom_with_company_uuid(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();

        $entity = new BomEntity(
            branch_uuid:  $branchA->uuid,
            company_uuid: $companyA->uuid,
            item_uuid:    (string) Str::uuid(),
            unit_uuid:    (string) Str::uuid(),
            qty:          2.0,
            description:  'Test BOM',
            is_active:    true,
        );

        $result = $this->repository->save($entity);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('boms', [
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
        ]);
    }

    // ---------------------------------------------------------------
    // findByUUID
    // ---------------------------------------------------------------

    public function test_find_by_uuid_returns_correct_bom(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        $uuid     = (string) Str::uuid();

        Bom::create([
            'uuid'         => $uuid,
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'BOM-FIND-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 3,
        ]);

        $result = $this->repository->findByUUID($uuid);

        $this->assertNotNull($result);
        $this->assertEquals($uuid, $result->uuid);
        $this->assertEquals($companyA->uuid, $result->company_uuid);
    }

    // ---------------------------------------------------------------
    // delete
    // ---------------------------------------------------------------

    public function test_delete_removes_bom(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        $uuid     = (string) Str::uuid();

        Bom::create([
            'uuid'         => $uuid,
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'BOM-DEL-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $result = $this->repository->delete($uuid, 'actor-uuid');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('boms', ['uuid' => $uuid, 'deleted_at' => 0]);
    }
}
