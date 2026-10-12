<?php

namespace Tests\Feature\Controllers\Finance\Journal;

use App\Modules\Finance\Journal\Http\Controllers\WebAccountingController;
use Tests\TestCase;

class WebAccountingControllerTest extends TestCase
{

    public function test_show_periods(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showPeriods');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_period(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'storePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_activate_period(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'activatePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_period(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'destroyPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_parent_accounts(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showParentAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_parent_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'storeParentAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_parent_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'updateParentAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_parent_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'destroyParentAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_sub_accounts(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showSubAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_sub_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'storeSubAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_sub_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'updateSubAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_sub_account(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'destroySubAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_chart_of_accounts(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showChartOfAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_opening_balance(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_opening_balance(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'storeOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_transactions(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showTransactions');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_transaction(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'storeTransaction');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_transaction(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'destroyTransaction');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_journals(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showJournals');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_general_ledger(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showGeneralLedger');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_trial_balance(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_trial_balance(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'exportTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_balance_sheet(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showBalanceSheet');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_income_statement(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showIncomeStatement');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_period_adjustments(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showPeriodAdjustments');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_balance_check(): void
    {
        $method = new \ReflectionMethod(WebAccountingController::class, 'showBalanceCheck');
        $this->assertTrue($method->isPublic());
    }
}