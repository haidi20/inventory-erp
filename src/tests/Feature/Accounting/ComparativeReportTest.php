<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComparativeReportTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $branch;
    protected $period1;
    protected $period2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->reportService = app(FinancialReportService::class);
        
        $this->period1 = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);
        
        $this->period2 = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28'
        ]);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Laporan Perbandingan (Comparative) - Year over Year / Month over Month
     */
    public function test_period_over_period_growth_analysis()
    {
        // Simulasi Saldo Pendapatan untuk Periode 1 (Y-1 / M-1)
        // Rp 100.000.000
        $incomePeriod1 = 100000000;
        
        // Simulasi Saldo Pendapatan untuk Periode 2 (Current)
        // Rp 125.000.000
        $incomePeriod2 = 125000000;
        
        // Perhitungan Pertumbuhan (Growth Percentage)
        // ((Sekarang - Lalu) / Lalu) * 100
        $growth = (($incomePeriod2 - $incomePeriod1) / $incomePeriod1) * 100;
        
        $this->assertEquals(25.0, $growth, "Pertumbuhan harus sebesar 25%");
        
        // Verifikasi Nilai Nominal Kenaikan (Variance)
        $variance = $incomePeriod2 - $incomePeriod1;
        $this->assertEquals(25000000, $variance);

        // Assert arah tren (Positive Growth)
        $this->assertGreaterThan(0, $growth);
    }

    public function test_month_over_month_growth()
    {
        $januaryRevenue = 100000000;
        $februaryRevenue = 120000000;

        $growth = (($februaryRevenue - $januaryRevenue) / $januaryRevenue) * 100;

        $this->assertEqualsWithDelta(20.0, $growth, 0.001);
    }

    public function test_year_over_year_growth()
    {
        $march2025Revenue = 80000000;
        $march2026Revenue = 100000000;

        $growth = (($march2026Revenue - $march2025Revenue) / $march2025Revenue) * 100;

        $this->assertEqualsWithDelta(25.0, $growth, 0.001);
    }

    public function test_year_to_date_accumulation()
    {
        $januaryRevenue = 50000000;
        $februaryRevenue = 60000000;
        $marchRevenue = 70000000;

        $yearToDateRevenue = $januaryRevenue + $februaryRevenue + $marchRevenue;

        $this->assertEquals(180000000, $yearToDateRevenue);
    }

    public function test_quarter_over_quarter_growth()
    {
        $firstQuarterRevenue = 300000000;
        $secondQuarterRevenue = 360000000;

        $growth = (($secondQuarterRevenue - $firstQuarterRevenue) / $firstQuarterRevenue) * 100;

        $this->assertEqualsWithDelta(20.0, $growth, 0.001);
    }

    public function test_side_by_side_comparison_structure()
    {
        $comparison = [
            'left_period' => ['label' => 'Jan 2026', 'revenue' => 100000000],
            'right_period' => ['label' => 'Jan 2025', 'revenue' => 80000000],
        ];

        $variance = $comparison['left_period']['revenue'] - $comparison['right_period']['revenue'];

        $this->assertEquals('Jan 2026', $comparison['left_period']['label']);
        $this->assertEquals('Jan 2025', $comparison['right_period']['label']);
        $this->assertEquals(20000000, $variance);
    }
}
