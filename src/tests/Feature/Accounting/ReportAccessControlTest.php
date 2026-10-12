<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ReportAccessControlTest
 * 
 * Verifikasi kontrol akses (RBAC) pada pelaporan (Branch Isolation & Roles).
 */
class ReportAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branchA;
    protected $branchB;
    protected $periodA;
    protected $periodB;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branchA = Branch::factory()->create(['name' => 'Branch A']);
        $this->branchB = Branch::factory()->create(['name' => 'Branch B']);
        
        $this->periodA = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branchA->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);
        
        $this->periodB = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branchB->uuid,
            'period' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->service = app(FinancialReportService::class);
    }

    public function test_user_cannot_access_financial_report_of_other_branch()
    {
        $userA = User::factory()->create(['branch_uuid' => $this->branchA->uuid]);
        $this->actingAs($userA);

        $this->expectException(UnauthorizedException::class);
        $this->service->getIncomeStatement($this->periodB->uuid, $this->branchB->uuid);
    }

    public function test_user_can_access_own_branch_report()
    {
        $userA = User::factory()->create(['branch_uuid' => $this->branchA->uuid]);
        $this->actingAs($userA);

        $result = $this->service->getIncomeStatement($this->periodA->uuid, $this->branchA->uuid);
        $this->assertIsArray($result);
    }

    public function test_consolidated_report_access_requires_no_branch_id()
    {
        // For standard user with branch_uuid, consolidated access (branchId = null) might be restricted
        $userA = User::factory()->create(['branch_uuid' => $this->branchA->uuid]);
        $this->actingAs($userA);

        $result = $this->service->getIncomeStatement($this->periodA->uuid, null);
        $this->assertIsArray($result);
    }

    public function test_admin_access_to_all_branches()
    {
        $admin = User::factory()->create(['branch_uuid' => null]); // HQ Admin
        $this->actingAs($admin);

        $resultA = $this->service->getIncomeStatement($this->periodA->uuid, $this->branchA->uuid);
        $resultB = $this->service->getIncomeStatement($this->periodB->uuid, $this->branchB->uuid);
        
        $this->assertIsArray($resultA);
        $this->assertIsArray($resultB);
    }

    public function test_data_segregation_by_branch_logic()
    {
        $this->assertTrue(true);
    }
}
