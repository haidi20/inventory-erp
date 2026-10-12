<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use Tests\TestCase;

class AccountSettingRepositoryTest extends TestCase
{

    public function test_all_accounts(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'allAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_account_by_code(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findAccountByCode');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_account_by_id(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findAccountById');
        $this->assertTrue($method->isPublic());
    }

    public function test_has_header_accounts(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'hasHeaderAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_mappings_by_menu(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findMappingsByMenu');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_mappings_by_prefix(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findMappingsByPrefix');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_mappings(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'getMappings');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_mapping(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'updateMapping');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_tax_settings(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'getTaxSettings');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_tax_setting_by_name(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findTaxSettingByName');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_tax_setting(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'updateTaxSetting');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_opening_balances(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'getOpeningBalances');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_opening_balance(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'updateOpeningBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period_by_date(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findPeriodByDate');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_closing_periods(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'getClosingPeriods');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_account_balance(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'updateAccountBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_or_update_closing_period(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'createOrUpdateClosingPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period_by_id(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findPeriodById');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_tax_setting_for_rate(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findTaxSettingForRate');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period_by_uuid_and_company(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findPeriodByUuidAndCompany');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period_by_period(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findPeriodByPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_has_inactive_accounts(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'hasInactiveAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_open_period(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findOpenPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_period(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findPeriod');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_branch(): void
    {
        $method = new \ReflectionMethod(AccountSettingRepository::class, 'findBranch');
        $this->assertTrue($method->isPublic());
    }
}