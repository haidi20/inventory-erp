<?php

namespace Tests\Unit\Services\Finance\Ledger;

use App\Modules\Finance\Ledger\Services\FinancialReportQueryService;
use Tests\TestCase;

class FinancialReportQueryServiceTest extends TestCase
{

    public function test_find_period(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'findPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period_by_period(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'findPeriodByPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_branch(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'findBranch');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_income_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getIncomeStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balance_sheet(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getBalanceSheet');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_cash_flow_indirect(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getCashFlowIndirect');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_equity_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getEquityStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_adjusted_trial_balance(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getAdjustedTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_a_r_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getARAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_a_p_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getAPAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_inventory_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getInventoryReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_fixed_asset_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getFixedAssetReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_tax_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportQueryService::class, 'getTaxReport');
        $this->assertTrue($method->isPublic());
    }
}