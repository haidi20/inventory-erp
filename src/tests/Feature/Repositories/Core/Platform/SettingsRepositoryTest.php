<?php

namespace Tests\Feature\Repositories\Core\Platform;

use App\Modules\Core\Platform\Repositories\SettingsRepository;
use Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{

    public function test_get_company_data(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getCompanyData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_satusehat_settings(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getSatusehatSettings');
        $this->assertTrue($method->isPublic());
    }

    public function test_save_satusehat_settings(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'saveSatusehatSettings');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_icd10_page_data(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getIcd10PageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'storeIcd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_icd10(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'deleteIcd10');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_database_management_data(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getDatabaseManagementData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_recent_backup_files(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getRecentBackupFiles');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_backup_history_datatable(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'getBackupHistoryDatatable');
        $this->assertTrue($method->isPublic());
    }

    public function test_export_database(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'exportDatabase');
        $this->assertTrue($method->isPublic());
    }

    public function test_resolve_backup_download(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'resolveBackupDownload');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_database(): void
    {
        $method = new \ReflectionMethod(SettingsRepository::class, 'restoreDatabase');
        $this->assertTrue($method->isPublic());
    }
}