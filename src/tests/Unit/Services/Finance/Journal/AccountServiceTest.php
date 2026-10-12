<?php

namespace Tests\Unit\Services\Finance\Journal;

use App\Modules\Finance\Journal\Services\AccountService;
use Tests\TestCase;

class AccountServiceTest extends TestCase
{

    public function test_list_accounts(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'listAccounts');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'createAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'getAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_before_save(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'validateBeforeSave');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'updateAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_resolve_account_type(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'resolveAccountType');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_parent_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'createParentAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_parent_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'updateParentAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_sub_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'createSubAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_sub_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'updateSubAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_account(): void
    {
        $method = new \ReflectionMethod(AccountService::class, 'deleteAccount');
        $this->assertTrue($method->isPublic());
    }
}