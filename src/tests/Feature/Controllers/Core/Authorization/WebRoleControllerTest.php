<?php

namespace Tests\Feature\Controllers\Core\Authorization;

use App\Modules\Core\Authorization\Http\Controllers\WebRoleController;
use Tests\TestCase;

class WebRoleControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'authorize');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_for_user(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'authorizeForUser');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_resource(): void
    {
        $method = new \ReflectionMethod(WebRoleController::class, 'authorizeResource');
        $this->assertTrue($method->isPublic());
    }
}