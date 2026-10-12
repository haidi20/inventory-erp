<?php

namespace Tests\Feature\Controllers\Core\Identity;

use App\Modules\Core\Identity\Http\Controllers\WebUserController;
use Tests\TestCase;

class WebUserControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }

    public function test_export(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'export');
        $this->assertTrue($method->isPublic());
    }

    public function test_import(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'import');
        $this->assertTrue($method->isPublic());
    }

    public function test_download_import_template(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'downloadImportTemplate');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'authorize');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_for_user(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'authorizeForUser');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_resource(): void
    {
        $method = new \ReflectionMethod(WebUserController::class, 'authorizeResource');
        $this->assertTrue($method->isPublic());
    }
}