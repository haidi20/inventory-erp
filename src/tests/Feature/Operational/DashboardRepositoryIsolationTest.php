<?php

namespace Tests\Feature\Operational;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithSandboxRuntime;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Repositories\DashboardRepository;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardRepositoryIsolationTest extends TestCase
{
    use RefreshDatabase, InteractsWithSandboxRuntime;

    protected DashboardRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DashboardRepository();
    }

    public function test_dashboard_data_is_isolated_per_company()
    {
        // 1. Setup Company A (Sandbox)
        $companyA = $this->sandboxCompany();
        $branchA = $this->sandboxBranch();
        
        // 2. Setup Company B
        $companyB = Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Company B',
            'is_active' => true,
        ]);
        $branchB = Branch::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'short_name' => 'B-B1',
            'name' => 'Branch B1',
            'is_active' => true,
        ]);

        $patientA = Patient::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid' => $branchA->uuid,
            'name' => 'Patient A',
            'medical_record_no' => 'RM-A',
        ]);

        $patientB = Patient::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid' => $branchB->uuid,
            'name' => 'Patient B',
            'medical_record_no' => 'RM-B',
        ]);

        // Seed Registrations
        Registration::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid' => $branchA->uuid,
            'patient_uuid' => $patientA->uuid,
            'status' => 'waiting',
            'registered_at' => Carbon::now()->timestamp,
        ]);

        Registration::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid' => $branchB->uuid,
            'patient_uuid' => $patientB->uuid,
            'status' => 'waiting',
            'registered_at' => Carbon::now()->timestamp,
        ]);

        // Seed Sales
        Sale::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyA->uuid,
            'branch_uuid' => $branchA->uuid,
            'sale_no' => 'SAL-A-001',
            'total_amount' => 1000,
            'paid' => 1000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => Carbon::now()->timestamp,
        ]);

        Sale::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid' => $branchB->uuid,
            'sale_no' => 'SAL-B-001',
            'total_amount' => 5000,
            'paid' => 5000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => Carbon::now()->timestamp,
        ]);

        // 3. Verify Isolation for Company A
        $this->assertEquals(1, $this->repository->getActiveQueueCount($companyA->uuid), 'Should only see antrian from Company A');
        $this->assertEquals(1000, $this->repository->getTodayPosRevenue($companyA->uuid), 'Should only see sales from Company A');
        $this->assertEquals(1, $this->repository->getPatientCount($companyA->uuid), 'Should only see patients from Company A');

        // 4. Verify Isolation for Company B
        $this->assertEquals(1, $this->repository->getActiveQueueCount($companyB->uuid), 'Should only see antrian from Company B');
        $this->assertEquals(5000, $this->repository->getTodayPosRevenue($companyB->uuid), 'Should only see sales from Company B');
        $this->assertEquals(1, $this->repository->getPatientCount($companyB->uuid), 'Should only see patients from Company B');
    }
}
