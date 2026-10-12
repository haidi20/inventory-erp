<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LongRunningStabilityTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialReportService $reportService;
    protected User $admin;
    protected Branch $branch;
    protected ClosingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->reportService = app(FinancialReportService::class);
        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
        $this->branch = Branch::findOrFail($this->admin->branch_uuid);
        $this->period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->orderByDesc('start_date')->firstOrFail();

        $this->actingAs($this->admin);
    }

    public function test_repeated_income_statement_generation_remains_stable(): void
    {
        $initialJournalCount = Journal::count();
        $results = [];

        $reportEntity = FinancialReportEntity::fromArray(['period_uuid' => $this->period->uuid, 'branch_uuid' => $this->branch->uuid]);
        for ($i = 0; $i < 25; $i++) {
            $report = $this->reportService->getIncomeStatement($reportEntity);
            $results[] = [
                'net_income' => $report['net_income'],
                'revenue_total' => $report['revenue']['total'],
                'expense_total' => $report['expense']['total'],
            ];
        }

        $this->assertCount(1, array_unique(array_map('json_encode', $results)));
        $this->assertEquals($initialJournalCount, Journal::count());
    }
}
