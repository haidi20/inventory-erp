<?php

namespace Tests\Feature\Controllers\Core\Platform;

use App\Modules\Core\Platform\Http\Controllers\SettingsController;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'icd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'storeIcd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'destroyIcd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_database(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'exportDatabase');
        $this->assertTrue($method->isPublic());
    }

    public function test_download_database_backup(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'downloadDatabaseBackup');
        $this->assertTrue($method->isPublic());
    }

    public function test_backup_history_datatable(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'backupHistoryDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_database(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'restoreDatabase');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_satusehat(): void
    {
        $method = new \ReflectionMethod(SettingsController::class, 'storeSatusehat');
        $this->assertTrue($method->isPublic());
    }
}