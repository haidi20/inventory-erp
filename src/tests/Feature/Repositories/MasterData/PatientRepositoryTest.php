<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Repositories\PatientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PatientRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PatientRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_patients()
    {
        Patient::factory()->count(25)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new PatientEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 10
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(10, $result['items']);
        $this->assertEquals(25, $result['total']);
        $this->assertEquals(3, $result['last_page']);
    }

    public function test_get_page_data_with_search()
    {
        Patient::factory()->create([
            'name' => 'Budi Santoso',
            'medical_record_no' => 'MR001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Patient::factory()->create([
            'name' => 'Siti Aminah',
            'medical_record_no' => 'MR002',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        // Search by name
        $entity = new PatientEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            search: 'Budi'
        );
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Budi Santoso', $result['items'][0]->name);

        // Search by MRN
        $entity->search = 'MR002';
        $result = $this->repository->getPageData($entity);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Siti Aminah', $result['items'][0]->name);
    }

    public function test_find_by_uuid_returns_correct_patient()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $searchEntity = new PatientEntity(
            uuid: $patient->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $found = $this->repository->findByUuid($searchEntity);

        $this->assertNotNull($found);
        $this->assertEquals($patient->uuid, $found->uuid);
        $this->assertEquals($patient->name, $found->name);
    }

    public function test_save_creates_new_patient()
    {
        $entity = new PatientEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            medical_record_no: 'MR-NEW-001',
            name: 'Pasien Baru',
            gender: 'L',
            birth_date: '1990-01-01',
            phone: '08123456789',
            is_active: true
        );

        $saved = $this->repository->save($entity);

        $this->assertDatabaseHas('patients', [
            'uuid' => $saved->uuid,
            'name' => 'Pasien Baru',
            'medical_record_no' => 'MR-NEW-001'
        ]);
    }

    public function test_save_updates_existing_patient()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Nama Lama'
        ]);

        $entity = PatientEntity::fromModel($patient);
        $entity->name = 'Nama Baru';

        $updated = $this->repository->save($entity);

        $this->assertEquals('Nama Baru', $updated->name);
        $this->assertDatabaseHas('patients', [
            'uuid' => $patient->uuid,
            'name' => 'Nama Baru'
        ]);
    }

    public function test_delete_performs_soft_delete()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'medical_record_no' => 'MR-DEL-001'
        ]);

        $entity = new PatientEntity(
            uuid: $patient->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $this->repository->delete($entity);

        // Verify soft deleted (deleted_at > 0)
        $this->assertDatabaseMissing('patients', [
            'uuid' => $patient->uuid,
            'deleted_at' => 0
        ]);

        $deletedPatient = Patient::withTrashed()->where('uuid', $patient->uuid)->first();
        $this->assertNotNull($deletedPatient->deleted_at);
        $this->assertNotEquals(0, $deletedPatient->deleted_at);

        // Verify we can create a new patient with SAME MRN in SAME branch because of our unique index standardization
        $newEntity = new PatientEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            medical_record_no: 'MR-DEL-001',
            name: 'Pasien Pengganti'
        );

        $saved = $this->repository->save($newEntity);
        $this->assertNotNull($saved->uuid);
        $this->assertNotEquals($patient->uuid, $saved->uuid);
    }

    public function test_get_page_data_only_returns_patients_from_the_same_company()
    {
        Patient::factory()->create([
            'name' => 'Pasien Sandbox',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Patient::factory()->create([
            'name' => 'Pasien Other',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $result = $this->repository->getPageData(new PatientEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 10,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame('Pasien Sandbox', $result['items'][0]->name);
        $this->assertSame(1, $result['totalPatients']);
    }
}
