<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveDataMaskingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $financeUser;
    protected User $basicUser;
    protected Branch $branch;
    protected ClosingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
        ]);
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'start_date' => strtotime('2026-01-01 00:00:00'),
            'end_date' => strtotime('2026-01-31 23:59:59'),
            'status' => 'open',
        ]);
        $this->basicUser = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->financeUser = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $apiFinancePermission = Permission::firstOrCreate(
            ['name' => 'finance.manage', 'guard_name' => 'web'],
            ['label' => 'Kelola Keuangan Umum API']
        );
        $this->financeUser->givePermissionTo($apiFinancePermission);

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_without_finance_permission_cannot_access_sensitive_report_data(): void
    {
        $response = $this->actingAs($this->basicUser, 'api')
            ->getJson("/api/v1/reports/income-statement?period_uuid={$this->period->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertForbidden()
            ->assertJson([
                'message' => 'User does not have the right permissions.',
            ]);
    }

    public function test_authorized_user_can_access_sensitive_report_totals(): void
    {
        $this->withoutMiddleware();
        $response = $this->actingAs($this->financeUser)
            ->getJson("/api/v1/reports/income-statement?period_uuid={$this->period->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('net_income', $data);
        $this->assertArrayHasKey('revenue', $data);
        $this->assertArrayHasKey('expense', $data);
    }
}
