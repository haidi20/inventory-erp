<?php

namespace Tests\Feature\Controllers\Core\Authorization;

use App\Modules\Core\Authorization\Http\Controllers\RoleController;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }

    public function test_export(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'export');
        $this->assertTrue($method->isPublic());
    }

    public function test_import(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'import');
        $this->assertTrue($method->isPublic());
    }

    public function test_download_template(): void
    {
        $method = new \ReflectionMethod(RoleController::class, 'downloadTemplate');
        $this->assertTrue($method->isPublic());
    }
}