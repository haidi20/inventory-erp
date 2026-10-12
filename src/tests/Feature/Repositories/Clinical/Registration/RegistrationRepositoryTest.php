<?php

namespace Tests\Feature\Repositories\Clinical\Registration;

use App\Modules\Clinical\Registration\Repositories\RegistrationRepository;
use Tests\TestCase;

class RegistrationRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(RegistrationRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(RegistrationRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(RegistrationRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(RegistrationRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(RegistrationRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}