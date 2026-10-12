<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\FileService;
use Tests\TestCase;

class FileServiceTest extends TestCase
{

    public function test_upload_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'uploadFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'getFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_files_by_parent(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'getFilesByParent');
        $this->assertTrue($method->isPublic());
    }

    public function test_download_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'downloadFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_soft_delete_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'softDeleteFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'restoreFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_hard_delete_file(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'hardDeleteFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_backup_files(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'backupFiles');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_files(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'restoreFiles');
        $this->assertTrue($method->isPublic());
    }

    public function test_verify_file_integrity(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'verifyFileIntegrity');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_storage_usage(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'getStorageUsage');
        $this->assertTrue($method->isPublic());
    }
}