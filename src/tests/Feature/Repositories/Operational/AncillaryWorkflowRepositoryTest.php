<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Modules\Clinical\MedicalRecord\Repositories\AncillaryWorkflowRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AncillaryWorkflowRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private AncillaryWorkflowRepository $repository;
    private Company $companySandbox;
    private Company $companyProduction;
    private Branch $branchSandbox;
    private Branch $branchProduction;
    private Person $patientSandbox;
    private Person $patientProduction;
    private Employee $doctorSandbox;
    private Employee $doctorProduction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AncillaryWorkflowRepository::class);

        // Create companies
        $this->companySandbox = Company::create([
            'uuid' => 'sandbox-company-uuid',
            'name' => 'Sandbox Company',
            'clinic_type' => 'klinik pratama',
            'is_active' => true,
        ]);

        $this->companyProduction = Company::create([
            'uuid' => 'production-company-uuid',
            'name' => 'Production Company',
            'clinic_type' => 'klinik utama',
            'is_active' => true,
        ]);

        // Create branches
        $this->branchSandbox = Branch::create([
            'uuid' => 'sandbox-branch-uuid',
            'name' => 'Sandbox Branch',
            'short_name' => 'SB',
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        $this->branchProduction = Branch::create([
            'uuid' => 'production-branch-uuid',
            'name' => 'Production Branch',
            'short_name' => 'PB',
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);

        // Create patients
        $this->patientSandbox = Person::create([
            'uuid' => 'sandbox-patient-uuid',
            'code' => 'PAT001',
            'name' => 'Pasien Sandbox',
            'type' => 'patient',
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        $this->patientProduction = Person::create([
            'uuid' => 'production-patient-uuid',
            'code' => 'PAT002',
            'name' => 'Pasien Production',
            'type' => 'patient',
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);

        // Create doctors
        $this->doctorSandbox = Employee::create([
            'uuid' => 'sandbox-doctor-uuid',
            'name' => 'Dr. Sandbox',
            'role' => 'dokter',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        $this->doctorProduction = Employee::create([
            'uuid' => 'production-doctor-uuid',
            'name' => 'Dr. Production',
            'role' => 'dokter',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_get_request_entry_data_filters_by_company()
    {
        // Repository should return empty arrays when called with company that has no orders
        $result = $this->repository->getRequestEntryData($this->companySandbox->uuid);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('lab', $result);
        $this->assertArrayHasKey('radiology', $result);
        $this->assertEquals(0, $result['lab']['pendingCount']);
        $this->assertEquals(0, $result['radiology']['pendingCount']);
    }

    /** @test */
    public function test_get_result_entry_data_filters_by_company()
    {
        // Repository should return empty arrays when called with company that has no orders
        $result = $this->repository->getResultEntryData($this->companyProduction->uuid);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('lab', $result);
        $this->assertArrayHasKey('radiology', $result);
        $this->assertEquals(0, $result['lab']['readyForResultCount']);
        $this->assertEquals(0, $result['radiology']['readyForResultCount']);
    }

    /** @test */
    public function test_get_request_entry_data_respects_company_isolation()
    {
        // Sandbox company should see only its data
        $sandboxResult = $this->repository->getRequestEntryData($this->companySandbox->uuid);
        $productionResult = $this->repository->getRequestEntryData($this->companyProduction->uuid);

        // Both should return 0 since no orders were created
        $this->assertEquals(0, $sandboxResult['lab']['pendingCount']);
        $this->assertEquals(0, $productionResult['lab']['pendingCount']);
    }

    /** @test */
    public function test_get_result_entry_data_respects_company_isolation()
    {
        // Production company should see only its data
        $sandboxResult = $this->repository->getResultEntryData($this->companySandbox->uuid);
        $productionResult = $this->repository->getResultEntryData($this->companyProduction->uuid);

        // Both should return 0 since no orders were created
        $this->assertEquals(0, $sandboxResult['radiology']['processingCount']);
        $this->assertEquals(0, $productionResult['radiology']['processingCount']);
    }
}
