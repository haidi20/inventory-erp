<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Repositories\EmployeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EmployeeRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_employees()
    {
        Employee::factory()->count(30)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 15
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(15, $result['items']);
        $this->assertEquals(30, $result['total']);
        $this->assertEquals(2, $result['last_page']);
    }

    public function test_get_page_data_with_search_and_role_filter()
    {
        Employee::factory()->create([
            'name' => 'Dr. Bambang',
            'role' => 'dokter',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'nik' => '1234567890123456'
        ]);

        Employee::factory()->create([
            'name' => 'Susi Susanti',
            'role' => 'perawat',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'nik' => '1234567890123457'
        ]);

        // Search by name
        $entity = new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            search: 'Bambang'
        );
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Dr. Bambang', $result['items'][0]->name);

        // Filter by role
        $entity = new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            filter_role: 'perawat'
        );
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Susi Susanti', $result['items'][0]->name);
    }

    public function test_save_creates_new_employee_and_supports_soft_delete_reentry()
    {
        $nik = '1234567890123456';
        
        // 1. Create and save new employee
        $entity = new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            nik: $nik,
            name: 'Karyawan A',
            role: 'staf',
            is_active: true
        );

        $saved = $this->repository->save($entity);
        $this->assertDatabaseHas('employees', ['uuid' => $saved->uuid, 'nik' => $nik]);

        // 2. Soft delete the employee
        $this->repository->delete($saved);
        
        // Verify it is soft deleted (deleted_at > 0)
        $deleted = Employee::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // 3. Create NEW employee with SAME NIK (should work because of our composite unique index)
        $newEntity = new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            nik: $nik,
            name: 'Karyawan B (Pengganti)',
            role: 'staf'
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
        $this->assertEquals($nik, $reSaved->nik);
    }

    public function test_save_updates_existing_employee()
    {
        $employee = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Nama Lama'
        ]);

        $entity = EmployeeEntity::fromModel($employee);
        $entity->name = 'Nama Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Nama Baru', $updated->name);
        $this->assertDatabaseHas('employees', [
            'uuid' => $employee->uuid,
            'name' => 'Nama Baru'
        ]);
    }

    public function test_find_by_uuid_returns_correct_employee()
    {
        $employee = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new EmployeeEntity(
            uuid: $employee->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $found = $this->repository->findByUuid($entity);

        $this->assertNotNull($found);
        $this->assertEquals($employee->uuid, $found->uuid);
    }

    public function test_get_page_data_only_returns_employees_from_the_same_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        Employee::factory()->create([
            'name' => 'Sandbox Employee',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Employee::factory()->create([
            'name' => 'Foreign Employee',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $result = $this->repository->getPageData(new EmployeeEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame('Sandbox Employee', $result['items'][0]->name);
    }
}
