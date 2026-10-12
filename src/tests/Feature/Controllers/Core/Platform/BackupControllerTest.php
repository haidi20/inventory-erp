<?php

namespace Tests\Feature\Controllers\Core\Platform;

use App\Modules\Core\Platform\Http\Controllers\BackupController;
use Tests\TestCase;

class BackupControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BackupController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_export(): void
    {
        $method = new \ReflectionMethod(BackupController::class, 'export');
        $this->assertTrue($method->isPublic());
    }

    public function test_download(): void
    {
        $method = new \ReflectionMethod(BackupController::class, 'download');
        $this->assertTrue($method->isPublic());
    }

    public function test_history_datatable(): void
    {
        $method = new \ReflectionMethod(BackupController::class, 'historyDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore(): void
    {
        $method = new \ReflectionMethod(BackupController::class, 'restore');
        $this->assertTrue($method->isPublic());
    }
}