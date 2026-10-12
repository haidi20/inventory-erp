<?php

namespace Tests\Feature\Repositories\Operational;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Clinical\Pharmacy\Models\Dispensing;
use App\Modules\Clinical\Pharmacy\Repositories\DispensingRepository;
use App\Modules\Clinical\Pharmacy\Entities\DispensingEntity;
use Illuminate\Support\Str;

class DispensingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DispensingRepository $repository;
    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private Patient $patientA;
    private Patient $patientB;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(DispensingRepository::class);

        // Create companies
        $this->companyA = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Company A',
            'clinic_type' => 'Klinik',
            'phone' => '0411000001',
            'email' => 'company-a@test.local',
            'address' => 'Street A',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        $this->companyB = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Company B',
            'clinic_type' => 'Klinik',
            'phone' => '0411000002',
            'email' => 'company-b@test.local',
            'address' => 'Street B',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        // Create branches
        $this->branchA = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Branch A',
            'short_name' => 'BA',
            'company_uuid' => $this->companyA->uuid,
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Branch B',
            'short_name' => 'BB',
            'company_uuid' => $this->companyB->uuid,
            'is_active' => true,
        ]);

        // Create user
        $this->user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test User',
            'email' => 'test@test.local',
            'password' => bcrypt('password'),
            'branch_uuid' => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_active' => true,
        ]);

        // Create patients
        $this->patientA = Patient::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Patient A',
            'medical_record_no' => 'MR-001',
            'branch_uuid' => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_active' => true,
        ]);

        $this->patientB = Patient::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Patient B',
            'medical_record_no' => 'MR-002',
            'branch_uuid' => $this->branchB->uuid,
            'company_uuid' => $this->companyB->uuid,
            'is_active' => true,
        ]);
    }

    public function test_get_paginated_filters_by_company()
    {
        // Create dispensings for each company
        Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-001',
            'patient_uuid' => $this->patientA->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'total' => 100000,
            'dispensed_at' => now()->timestamp,
        ]);

        Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-002',
            'patient_uuid' => $this->patientB->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'total' => 200000,
            'dispensed_at' => now()->timestamp,
        ]);

        // Query without company filter (old behavior - reveals the bug)
        $result = $this->repository->getPaginated([]);

        // Currently returns both (shows the bug exists)
        $this->assertEquals(2, $result->total());
    }

    public function test_build_query_respects_company_scope()
    {
        Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-101',
            'patient_uuid' => $this->patientA->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'total' => 100000,
            'dispensed_at' => now()->timestamp,
        ]);

        Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-102',
            'patient_uuid' => $this->patientB->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'total' => 200000,
            'dispensed_at' => now()->timestamp,
        ]);

        // Verify records exist
        $all = Dispensing::all();
        $this->assertEquals(2, $all->count());

        // After fix: query should filter by company
        $query = $this->repository->buildQuery(['company_uuid' => $this->companyA->uuid]);
        $results = $query->get();
        $this->assertEquals(1, $results->count(), "Expected 1 record for company A, got {$results->count()}. Company filter: {$this->companyA->uuid}");
        $this->assertEquals('DISP-101', $results->first()->dispensing_no);
    }

    public function test_company_isolation_prevents_data_leakage()
    {
        $disp_a = Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-COMPANY-A',
            'patient_uuid' => $this->patientA->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'total' => 100000,
            'dispensed_at' => now()->timestamp,
        ]);

        $disp_b = Dispensing::create([
            'uuid' => (string) Str::uuid(),
            'dispensing_no' => 'DISP-COMPANY-B',
            'patient_uuid' => $this->patientB->uuid,
            'dispensed_by_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'total' => 200000,
            'dispensed_at' => now()->timestamp,
        ]);

        // Company A should only see its own dispensings
        $queryA = $this->repository->buildQuery(['company_uuid' => $this->companyA->uuid]);
        $this->assertEquals(1, $queryA->count());
        $this->assertEquals($disp_a->uuid, $queryA->first()->uuid);

        // Company B should only see its own dispensings
        $queryB = $this->repository->buildQuery(['company_uuid' => $this->companyB->uuid]);
        $this->assertEquals(1, $queryB->count());
        $this->assertEquals($disp_b->uuid, $queryB->first()->uuid);
    }
}
