<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\BackupService;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_export(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'export');
        $this->assertTrue($method->isPublic());
    }

    public function test_prepare_export(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'prepareExport');
        $this->assertTrue($method->isPublic());
    }

    public function test_download(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'download');
        $this->assertTrue($method->isPublic());
    }

    public function test_history_datatable(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'historyDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore(): void
    {
        $method = new \ReflectionMethod(BackupService::class, 'restore');
        $this->assertTrue($method->isPublic());
    }
}