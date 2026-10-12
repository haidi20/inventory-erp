<?php

namespace Tests\Unit\Services\Finance\Journal;

use App\Modules\Finance\Journal\Services\AccountingCycleService;
use Tests\TestCase;

class AccountingCycleServiceTest extends TestCase
{

    public function test_create_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'createPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_activate_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'activatePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'deletePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_period_exists_for_company(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'periodExistsForCompany');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_adjusting_journals(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateAdjustingJournals');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_adjusting_journal(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateAdjustingJournal');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_accrued_expense(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateAccruedExpense');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_accrued_revenue(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateAccruedRevenue');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_prepaid_expense(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generatePrepaidExpense');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_unearned_revenue(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateUnearnedRevenue');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_depreciation(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateDepreciation');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_allowance_for_doubts(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'generateAllowanceForDoubts');
        $this->assertTrue($method->isPublic());
    }

    public function test_close_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'closePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_post_closing_trial_balance(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'getPostClosingTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_initialize_new_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'initializeNewPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_cash_flow(): void
    {
        $method = new \ReflectionMethod(AccountingCycleService::class, 'getCashFlow');
        $this->assertTrue($method->isPublic());
    }
}