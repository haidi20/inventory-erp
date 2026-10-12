<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\AccountingCycleRepository;
use Tests\TestCase;

class AccountingCycleRepositoryTest extends TestCase
{

    public function test_create_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'createPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_activate_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'activatePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_period(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'deletePeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_period_exists_for_company(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'periodExistsForCompany');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_adjusting_journals(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'generateAdjustingJournals');
        $this->assertTrue($method->isPublic());
    }

    public function test_generate_closing_journals(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'generateClosingJournals');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_post_closing_trial_balance(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'getPostClosingTrialBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_initialize_opening_balance(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'initializeOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_period_for_adjustment(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'validatePeriodForAdjustment');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_adjusting_journal(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'createAdjustingJournal');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_cash_flow_indirect(): void
    {
        $method = new \ReflectionMethod(AccountingCycleRepository::class, 'getCashFlowIndirect');
        $this->assertTrue($method->isPublic());
    }
}