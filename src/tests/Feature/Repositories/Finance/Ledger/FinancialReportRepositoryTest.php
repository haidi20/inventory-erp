<?php

namespace Tests\Feature\Repositories\Finance\Ledger;

use App\Modules\Finance\Ledger\Repositories\FinancialReportRepository;
use Tests\TestCase;

class FinancialReportRepositoryTest extends TestCase
{

    public function test_get_income_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getIncomeStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balance_sheet(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getBalanceSheet');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_cash_flow_indirect(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getCashFlowIndirect');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_adjusted_trial_balance(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getAdjustedTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_equity_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getEquityStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_a_r_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getARAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_a_p_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getAPAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_inventory_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getInventoryReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_fixed_asset_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getFixedAssetReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_tax_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportRepository::class, 'getTaxReport');
        $this->assertTrue($method->isPublic());
    }
}