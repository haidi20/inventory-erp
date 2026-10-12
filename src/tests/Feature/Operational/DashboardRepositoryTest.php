<?php

namespace Tests\Feature\Operational;

use App\Modules\Core\Organization\Models\Branch;
use Carbon\Carbon;
use App\Modules\Operational\Sales\Repositories\DashboardRepository;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use Spatie\Permission\Models\Role;
use App\Modules\Operational\Sales\Models\Sale;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class DashboardRepositoryTest extends TestCase
{
    protected DashboardRepository $repository;
    protected Branch $branch;
    protected string $companyUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DashboardRepository();

        // Company sungguhan: pivot user_companies/user_branches (dibuat otomatis
        // oleh compat mutator User saat branch_uuid di-set) punya FK ke companies.
        $company = \App\Modules\Core\Organization\Models\Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Klinik Pusat Company',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $company->uuid,
            'short_name' => 'B01',
            'name' => 'Klinik Pusat',
            'is_active' => true,
        ]);
        $this->companyUuid = $this->branch->company_uuid;

        $user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid
        ]);
        $this->actingAs($user);

        $patient = Patient::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->companyUuid,
            'name' => 'Test Patient',
            'nik' => '1234567890123456',
            'medical_record_no' => 'RM-TEST-001',
            'is_active' => true,
        ]);
        
        // Seed dummy Registration (Antrian)
        Registration::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->companyUuid,
            'registration_no' => 'REG-001',
            'date' => Carbon::now()->toDateString(),
            'registered_at' => Carbon::now()->timestamp,
            'status' => 'waiting',
            'patient_uuid' => $patient->uuid,
            'patient_type' => 'umum',
        ]);
        
        Registration::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->companyUuid,
            'registration_no' => 'REG-002',
            'date' => Carbon::now()->toDateString(),
            'registered_at' => Carbon::now()->timestamp,
            'status' => 'in_progress',
            'patient_uuid' => $patient->uuid,
            'patient_type' => 'umum',
        ]);

        // Seed other branch reg to ensure isolation
        $otherCompany = \App\Modules\Core\Organization\Models\Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Klinik Cabang Company',
            'is_active' => true,
        ]);
        $otherBranch = Branch::create([
            'uuid' => Str::uuid(),
            'company_uuid' => $otherCompany->uuid,
            'short_name' => 'B02',
            'name' => 'Klinik Cabang',
            'is_active' => true,
        ]);

        Registration::create([
            'uuid' => Str::uuid(),
            'branch_uuid' => $otherBranch->uuid, // Use a valid existing branch
            'company_uuid' => $otherBranch->company_uuid,
            'registration_no' => 'REG-003',
            'date' => Carbon::now()->toDateString(),
            'registered_at' => Carbon::now()->timestamp,
            'status' => 'waiting',
            'patient_uuid' => $patient->uuid,
            'patient_type' => 'umum',
        ]);
    }

    public function test_get_active_queue_count_returns_correct_count()
    {
        $count = $this->repository->getActiveQueueCount($this->companyUuid);
        
        // Seharusnya ada 2 antrian aktif (waiting + in_progress) yang ada di branch B01 pada hari ini
        $this->assertEquals(2, $count);
    }

    public function test_get_daily_stats_returns_structured_data()
    {
        $stats = $this->repository->getDailyRevenueStats($this->companyUuid, 30);
        
        $this->assertArrayHasKey('dailyChartLabels', $stats);
        $this->assertArrayHasKey('dailyServiceRevenueSeries', $stats);
        $this->assertArrayHasKey('dailyPosRevenueSeries', $stats);
        
        // Cek data yang dikembalikan ada 30 hari sesuai parameter
        $this->assertCount(30, $stats['dailyChartLabels']);
    }
}
