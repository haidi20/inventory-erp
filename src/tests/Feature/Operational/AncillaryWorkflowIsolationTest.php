<?php

namespace Tests\Feature\Operational;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithSandboxRuntime;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\MedicalRecord\Repositories\AncillaryWorkflowRepository;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AncillaryWorkflowIsolationTest extends TestCase
{
    use RefreshDatabase, InteractsWithSandboxRuntime;

    protected AncillaryWorkflowRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AncillaryWorkflowRepository();
    }

    public function test_ancillary_workflow_data_is_isolated_per_company()
    {
        $companyA = $this->sandboxCompany();
        $branchA = $this->sandboxBranch();
        $patientA = Patient::factory()->create([
            'branch_uuid' => $branchA->uuid,
            'company_uuid' => $companyA->uuid,
        ]);

        $companyB = Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Company B',
            'is_active' => true,
        ]);
        $branchB = Branch::factory()->create([
            'company_uuid' => $companyB->uuid,
        ]);
        $patientB = Patient::factory()->create([
            'branch_uuid' => $branchB->uuid,
            'company_uuid' => $companyB->uuid,
        ]);

        LabOrder::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $branchA->uuid,
            'company_uuid' => $companyA->uuid,
            'order_no' => 'LAB-A-001',
            'patient_uuid' => $patientA->uuid,
            'status' => 'pending',
            'ordered_at' => Carbon::now()->timestamp,
        ]);

        LabOrder::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $branchB->uuid,
            'company_uuid' => $companyB->uuid,
            'order_no' => 'LAB-B-001',
            'patient_uuid' => $patientB->uuid,
            'status' => 'pending',
            'ordered_at' => Carbon::now()->timestamp,
        ]);

        $dataA = $this->repository->getRequestEntryData($companyA->uuid);
        $this->assertEquals(1, $dataA['lab']['pendingCount']);
        $this->assertSame('LAB-A-001', $dataA['lab']['latestOrder']?->order_no);

        $dataB = $this->repository->getRequestEntryData($companyB->uuid);
        $this->assertEquals(1, $dataB['lab']['pendingCount']);
        $this->assertSame('LAB-B-001', $dataB['lab']['latestOrder']?->order_no);
    }
}
