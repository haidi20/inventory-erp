<?php

namespace Tests\Unit\Services\Analytics\Reports;

use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Tests\TestCase;

class FinancialReportServiceTest extends TestCase
{

    public function test_get_income_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getIncomeStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_balance_sheet(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getBalanceSheet');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_cash_flow(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getCashFlow');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_equity_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getEquityStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_adjusted_trial_balance(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getAdjustedTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_accounts_receivable_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getAccountsReceivableAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_accounts_payable_aging(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getAccountsPayableAging');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_inventory_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getInventoryReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_fixed_asset_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getFixedAssetReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_tax_report(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'getTaxReport');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_income_statement(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'exportIncomeStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_balance_sheet(): void
    {
        $method = new \ReflectionMethod(FinancialReportService::class, 'exportBalanceSheet');
        $this->assertTrue($method->isPublic());
    }
}