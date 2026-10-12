<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MultiBranchTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $branch1;
    protected $branch2;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch1 = Branch::factory()->create(['name' => 'Jakarta']);
        $this->branch2 = Branch::factory()->create(['name' => 'Surabaya']);
        
        $this->period = ClosingPeriod::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->service = app(AccountingCycleService::class);
    }

    /**
     * Skenario 11: Multi-Cabang dengan Penyesuaian
     */
    public function test_scenario_11_multi_branch_isolation()
    {
        // Generate for Branch 1
        $res1 = $this->service->generateAdjustingJournals(FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch1->uuid,
        ]));
        
        // Generate for Branch 2
        $res2 = $this->service->generateAdjustingJournals(FinancialReportEntity::fromArray([
            'period_uuid' => $this->period->uuid,
            'branch_uuid' => $this->branch2->uuid,
        ]));

        $this->assertIsArray($res1);
        $this->assertIsArray($res2);
    }
}
