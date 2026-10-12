<?php

namespace Tests\Feature\Controllers\Core\Identity;

use App\Modules\Core\Identity\Http\Controllers\WebAuthController;
use Tests\TestCase;

class WebAuthControllerTest extends TestCase
{

    public function test_show_login(): void
    {
        $method = new \ReflectionMethod(WebAuthController::class, 'showLogin');
        $this->assertTrue($method->isPublic());
    }

    public function test_pre_check(): void
    {
        $method = new \ReflectionMethod(WebAuthController::class, 'preCheck');
        $this->assertTrue($method->isPublic());
    }

    public function test_login(): void
    {
        $method = new \ReflectionMethod(WebAuthController::class, 'login');
        $this->assertTrue($method->isPublic());
    }

    public function test_change_password(): void
    {
        $method = new \ReflectionMethod(WebAuthController::class, 'changePassword');
        $this->assertTrue($method->isPublic());
    }

    public function test_logout(): void
    {
        $method = new \ReflectionMethod(WebAuthController::class, 'logout');
        $this->assertTrue($method->isPublic());
    }
}