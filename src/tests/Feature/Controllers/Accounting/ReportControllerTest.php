<?php

namespace Tests\Feature\Controllers\Accounting;

use App\Modules\Finance\Journal\Entities\PeriodInfoEntity;
use App\Modules\Finance\Journal\Entities\TotalsEntity;
use App\Modules\Finance\Ledger\Entities\TrialBalanceEntity;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportControllerTest extends TestCase
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

    public function test_can_access_trial_balance_for_current_company(): void
    {
        $period = $this->makePeriod($this->branch, $this->company);

        $trialBalance = new TrialBalanceEntity(
            new PeriodInfoEntity($period->uuid, 'April 2026', '2026-04-01', '2026-04-30', 'open'),
            $this->branch->uuid,
            [],
            new TotalsEntity(0, 0, 0),
            true,
            (string) time()
        );

        $this->mock(TrialBalanceService::class, function ($mock) use ($period, $trialBalance) {
            $mock->shouldReceive('generate')
                ->once()
                ->with($period->uuid, $this->branch->uuid)
                ->andReturn($trialBalance);
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/trial-balance?period_uuid={$period->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('metadata.period_name', 'April 2026');
    }

    public function test_cannot_access_trial_balance_for_period_from_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherPeriod = $this->makePeriod($otherBranch, $otherCompany);

        $this->mock(TrialBalanceService::class, function ($mock) {
            $mock->shouldNotReceive('generate');
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/trial-balance?period_uuid={$otherPeriod->uuid}&branch_uuid={$this->branch->uuid}");

        $response->assertNotFound();
    }

    public function test_cannot_access_trial_balance_for_branch_from_another_company(): void
    {
        $period = $this->makePeriod($this->branch, $this->company);
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        $this->mock(TrialBalanceService::class, function ($mock) {
            $mock->shouldNotReceive('generate');
        });

        $response = $this->actingAs($this->user)->getJson("/api/v1/reports/trial-balance?period_uuid={$period->uuid}&branch_uuid={$otherBranch->uuid}");

        $response->assertNotFound();
    }

    public function test_trial_balance_requires_period_uuid(): void
    {
        $this->mock(TrialBalanceService::class, function ($mock) {
            $mock->shouldNotReceive('generate');
        });

        $response = $this->actingAs($this->user)->getJson('/api/v1/reports/trial-balance');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
