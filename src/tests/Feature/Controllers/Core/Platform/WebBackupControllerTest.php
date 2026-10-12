<?php

namespace Tests\Feature\Controllers\Core\Platform;

use App\Modules\Core\Platform\Http\Controllers\WebBackupController;
use Tests\TestCase;

class WebBackupControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_export(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'export');
        $this->assertTrue($method->isPublic());
    }

    public function test_download(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'download');
        $this->assertTrue($method->isPublic());
    }

    public function test_history_datatable(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'historyDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'restore');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'authorize');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_for_user(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'authorizeForUser');
        $this->assertTrue($method->isPublic());
    }

    public function test_authorize_resource(): void
    {
        $method = new \ReflectionMethod(WebBackupController::class, 'authorizeResource');
        $this->assertTrue($method->isPublic());
    }
}