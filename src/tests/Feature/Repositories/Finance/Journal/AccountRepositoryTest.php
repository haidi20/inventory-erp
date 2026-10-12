<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\AccountRepository;
use Tests\TestCase;

class AccountRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_exists_in_context(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'existsInContext');
        $this->assertTrue($method->isPublic());
    }

    public function test_code_exists(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'codeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_parent_by_code(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'findParentByCode');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(AccountRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}