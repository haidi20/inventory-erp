<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\SettingsService;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_database(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'database');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_database(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'exportDatabase');
        $this->assertTrue($method->isPublic());
    }

    public function test_prepare_database_export(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'prepareDatabaseExport');
        $this->assertTrue($method->isPublic());
    }

    public function test_download_database_backup(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'downloadDatabaseBackup');
        $this->assertTrue($method->isPublic());
    }

    public function test_backup_history_datatable(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'backupHistoryDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_database(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'restoreDatabase');
        $this->assertTrue($method->isPublic());
    }

    public function test_satusehat(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'satusehat');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_satusehat(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'storeSatusehat');
        $this->assertTrue($method->isPublic());
    }

    public function test_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'icd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'storeIcd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsService::class, 'destroyIcd10');
        $this->assertTrue($method->isPublic());
    }
}