<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Manufacturing\Models\Assembly;
use App\Modules\Operational\Manufacturing\Repositories\AssemblyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Tests\TestCase;

class AssemblyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    use RefreshDatabase, InteractsWithSandboxRuntime;

    private AssemblyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AssemblyRepository();
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
    // index isolation
    // ---------------------------------------------------------------

    public function test_index_returns_assemblies_of_own_company_only(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        [$companyB, $branchB] = $this->createCompanyB();

        Assembly::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'AS-A-001',
            'date'         => time(),
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'quantity'     => 1,
            'status'       => 'draft',
        ]);

        Assembly::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid'  => $branchB->uuid,
            'document_no'  => 'AS-B-001',
            'date'         => time(),
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'quantity'     => 1,
            'status'       => 'draft',
        ]);

        $entity = new AssemblyEntity(company_uuid: $companyA->uuid, page: 1, per_page: 15);
        $result = $this->repository->index($entity);

        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);
        /** @var AssemblyEntity $first */
        $first = $result['items'][0];
        $this->assertEquals($companyA->uuid, $first->company_uuid);
    }

    public function test_index_does_not_return_other_company_assemblies(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        [$companyB, $branchB] = $this->createCompanyB();

        Assembly::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
            'document_no'  => 'AS-A-002',
            'date'         => time(),
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'quantity'     => 1,
            'status'       => 'draft',
        ]);

        $entity = new AssemblyEntity(company_uuid: $companyB->uuid, page: 1, per_page: 15);
        $result = $this->repository->index($entity);

        $this->assertCount(0, $result['items']);
        $this->assertEquals(0, $result['total']);
    }

    // ---------------------------------------------------------------
    // save
    // ---------------------------------------------------------------

    public function test_save_creates_assembly_with_company_uuid(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();

        $entity = new AssemblyEntity(
            branch_uuid:   $branchA->uuid,
            company_uuid:  $companyA->uuid,
            item_uuid:     (string) Str::uuid(),
            unit_uuid:     (string) Str::uuid(),
            location_uuid: (string) Str::uuid(),
            assembly_no:   'AS-SAVE-001',
            date:          time(),
            qty:           2.0,
            status:        'draft',
        );

        $result = $this->repository->save($entity);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('assemblies', [
            'company_uuid' => $companyA->uuid,
            'branch_uuid'  => $branchA->uuid,
        ]);
    }

    // ---------------------------------------------------------------
    // findByID
    // ---------------------------------------------------------------

    public function test_find_by_id_returns_correct_assembly(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        $uuid     = (string) Str::uuid();

        Assembly::create([
            'uuid'           => $uuid,
            'company_uuid'   => $companyA->uuid,
            'branch_uuid'    => $branchA->uuid,
            'document_no'    => 'AS-FIND-001',
            'date'           => time(),
            'item_uuid'      => (string) Str::uuid(),
            'unit_uuid'      => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'quantity'       => 3,
            'status'         => 'draft',
        ]);

        $result = $this->repository->findByID($uuid);

        $this->assertNotNull($result);
        $this->assertEquals($uuid, $result->uuid);
        $this->assertEquals($companyA->uuid, $result->company_uuid);
    }

    // ---------------------------------------------------------------
    // delete
    // ---------------------------------------------------------------

    public function test_delete_removes_assembly(): void
    {
        $companyA = $this->sandboxCompany();
        $branchA  = $this->sandboxBranch();
        $uuid     = (string) Str::uuid();

        Assembly::create([
            'uuid'           => $uuid,
            'company_uuid'   => $companyA->uuid,
            'branch_uuid'    => $branchA->uuid,
            'document_no'    => 'AS-DEL-001',
            'date'           => time(),
            'item_uuid'      => (string) Str::uuid(),
            'unit_uuid'      => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'quantity'       => 1,
            'status'         => 'draft',
        ]);

        $result = $this->repository->delete($uuid);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('assemblies', ['uuid' => $uuid, 'deleted_at' => 0]);
    }
}
