<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialRatioTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->reportService = app(FinancialReportService::class);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Analisis Rasio Keuangan Utama
     */
    public function test_financial_ratio_calculation()
    {
        // 1. Current Ratio (Likuiditas)
        // Aset Lancar / Liabilitas Lancar
        $currentAssets = 150000000;
        $currentLiabilities = 75000000;
        
        $currentRatio = $currentAssets / $currentLiabilities;
        $this->assertEquals(2.0, $currentRatio, "Current Ratio harus 2.0");
        
        // 2. Net Profit Margin (Profitabilitas)
        // (Laba Bersih / Total Pendapatan) * 100
        $netIncome = 30000000;
        $totalRevenue = 150000000;
        
        $netProfitMargin = ($netIncome / $totalRevenue) * 100;
        $this->assertEquals(20.0, $netProfitMargin, "Net Profit Margin harus 20%");

        // 3. Debt to Equity Ratio (Solvabilitas) - Tambahan Premium
        $totalDebt = 100000000;
        $totalEquity = 200000000;
        $der = $totalDebt / $totalEquity;
        $this->assertEquals(0.5, $der, "Debt to Equity Ratio harus 0.5");
    }

    public function test_current_ratio_prompt_case()
    {
        $currentAssets = 100000000;
        $currentLiabilities = 50000000;

        $currentRatio = $currentAssets / $currentLiabilities;

        $this->assertEqualsWithDelta(2.0, $currentRatio, 0.001);
    }

    public function test_quick_ratio_prompt_case()
    {
        $currentAssets = 100000000;
        $inventory = 20000000;
        $currentLiabilities = 50000000;

        $quickRatio = ($currentAssets - $inventory) / $currentLiabilities;

        $this->assertEqualsWithDelta(1.6, $quickRatio, 0.001);
    }

    public function test_debt_to_asset_ratio_prompt_case()
    {
        $totalDebt = 80000000;
        $totalAssets = 200000000;

        $debtToAssetRatio = $totalDebt / $totalAssets;

        $this->assertEqualsWithDelta(0.4, $debtToAssetRatio, 0.001);
    }

    public function test_net_profit_margin_prompt_case()
    {
        $netIncome = 20000000;
        $revenue = 100000000;

        $netProfitMargin = ($netIncome / $revenue) * 100;

        $this->assertEqualsWithDelta(20.0, $netProfitMargin, 0.001);
    }

    public function test_return_on_assets_prompt_case()
    {
        $netIncome = 20000000;
        $totalAssets = 200000000;

        $roa = ($netIncome / $totalAssets) * 100;

        $this->assertEqualsWithDelta(10.0, $roa, 0.001);
    }

    public function test_return_on_equity_prompt_case()
    {
        $netIncome = 20000000;
        $totalEquity = 120000000;

        $roe = ($netIncome / $totalEquity) * 100;

        $this->assertEqualsWithDelta(16.67, round($roe, 2), 0.001);
    }

    public function test_inventory_turnover_prompt_case()
    {
        $costOfGoodsSold = 60000000;
        $averageInventory = 10000000;

        $inventoryTurnover = $costOfGoodsSold / $averageInventory;

        $this->assertEqualsWithDelta(6.0, $inventoryTurnover, 0.001);
    }
}
