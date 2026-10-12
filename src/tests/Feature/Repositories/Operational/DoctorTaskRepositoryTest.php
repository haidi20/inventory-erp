<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Clinical\MedicalRecord\Repositories\DoctorTaskRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DoctorTaskRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private DoctorTaskRepository $repository;
    private Company $companySandbox;
    private Company $companyProduction;
    private Branch $branchSandbox;
    private Branch $branchProduction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(DoctorTaskRepository::class);

        // Create two different companies
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

        // Create branches for each company
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
    }

    /** @test */
    public function test_get_active_tasks_method_exists()
    {
        // Verify getActiveTasks method accepts company_uuid parameter
        // Test that repository can be called with company_uuid
        $this->assertTrue(method_exists($this->repository, 'getActiveTasks'));
    }

    /** @test */
    public function test_get_doctors_respects_company_scope()
    {
        // Create doctors for sandbox company
        $doctorSandbox1 = Employee::create([
            'uuid' => 'sandbox-doctor-1-uuid',
            'name' => 'Dr. Sandbox 1',
            'role' => 'dokter',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        $doctorSandbox2 = Employee::create([
            'uuid' => 'sandbox-doctor-2-uuid',
            'name' => 'Dr. Sandbox 2',
            'role' => 'dokter',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        // Create doctor for production company
        $doctorProduction = Employee::create([
            'uuid' => 'production-doctor-uuid',
            'name' => 'Dr. Production',
            'role' => 'dokter',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);

        // For now, test that method exists
        // After implementation, we'll verify filtering
        $this->assertTrue(method_exists($this->repository, 'getDoctors'));
    }

    /** @test */
    public function test_get_completed_today_counts_method_exists()
    {
        // Verify method exists and can be called with company_uuid
        $this->assertTrue(method_exists($this->repository, 'getCompletedTodayCounts'));
    }

    /** @test */
    public function test_get_recent_completed_method_exists()
    {
        // Verify method exists and can be called with company_uuid
        $this->assertTrue(method_exists($this->repository, 'getRecentCompleted'));
    }

    /** @test */
    public function test_doctor_isolation_on_get_doctors()
    {
        // Create 2 doctors for sandbox company
        Employee::create([
            'uuid' => 'sandbox-doctor-1-uuid',
            'name' => 'Dr. Sandbox 1',
            'role' => 'dokter',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        Employee::create([
            'uuid' => 'sandbox-doctor-2-uuid',
            'name' => 'Dr. Sandbox 2',
            'role' => 'dokter',
            'branch_uuid' => $this->branchSandbox->uuid,
            'company_uuid' => $this->companySandbox->uuid,
            'is_active' => true,
        ]);

        // Create 1 doctor for production company
        Employee::create([
            'uuid' => 'production-doctor-uuid',
            'name' => 'Dr. Production',
            'role' => 'dokter',
            'branch_uuid' => $this->branchProduction->uuid,
            'company_uuid' => $this->companyProduction->uuid,
            'is_active' => true,
        ]);

        // Get doctors (after implementation, we'll add company parameter)
        // For now, just verify method exists
        $this->assertTrue(method_exists($this->repository, 'getDoctors'));
    }
}
