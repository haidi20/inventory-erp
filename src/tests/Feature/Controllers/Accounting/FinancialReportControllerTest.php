<?php

namespace Tests\Feature\Controllers\Accounting;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancialReportControllerTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->withoutMiddleware();
    }

    private function makePeriod(Branch $branch, Company $company, array $overrides = []): ClosingPeriod
    {
        return ClosingPeriod::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'period' => '2026-04',
            'name' => 'April 2026',
            'start_date' => strtotime('2026-04-01'),
            'end_date' => strtotime('2026-04-30'),
            'status' => 'open',
        ], $overrides));
    }

    public function test_can_access_income_statement_for_current_company(): void
    {
        $period = $this->makePeriod($this->branch, $this->company);

        $this->mock(FinancialReportService::class, function ($mock) use ($period) {
            $mock->shouldReceive('getIncomeStatement')
                ->once()
                ->with(\Mockery::on(function (FinancialReportEntity $entity) use ($period) {
                    return $entity->period_uuid === $period->uuid
                        && $entity->branch_uuid === $this->branch->uuid
                        && $entity->company_uuid === $this->company->uuid;
                }))
                ->andReturn([
                    'period_info' => ['period_name' => 'April 2026'],
                    'branch_info' => ['uuid' => $this->branch->uuid],
                    'revenue' => ['total' => 1000],
                    'expense' => ['total' => 200],
                    'net_income' => 800,
                    'details' => [],
                ]);
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/income-statement?period_uuid={$period->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.net_income', 800);
    }

    public function test_cannot_access_income_statement_for_period_from_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherPeriod = $this->makePeriod($otherBranch, $otherCompany);

        $this->mock(FinancialReportService::class, function ($mock) {
            $mock->shouldNotReceive('getIncomeStatement');
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/income-statement?period_uuid={$otherPeriod->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertNotFound();
    }

    public function test_cannot_access_income_statement_for_branch_from_another_company(): void
    {
        $period = $this->makePeriod($this->branch, $this->company);
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $this->mock(FinancialReportService::class, function ($mock) {
            $mock->shouldNotReceive('getIncomeStatement');
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/income-statement?period_uuid={$period->uuid}&branch_uuid={$otherBranch->uuid}");

        $response->assertNotFound();
    }
}
