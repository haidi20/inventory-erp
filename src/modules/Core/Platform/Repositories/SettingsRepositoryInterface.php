<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Platform\Entities\Icd10Entity;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Core\Platform\Entities\SettingsEntity;

/**
 * SettingsRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola pengaturan sistem dan metadata medis.
 */
interface SettingsRepositoryInterface
{
    /**
     * Mendapatkan profil utama perusahaan/klinik.
     * 
     * @return array
     */
    public function getCompanyData(): array;

    /**
     * Mendapatkan konfigurasi integrasi SATUSEHAT.
     * 
     * @return array
     */
    public function getSatusehatSettings(): array;

    /**
     * Menyimpan atau memperbarui pengaturan SATUSEHAT.
     * 
     * @param SatusehatSettingsEntity $entity
     * @return void
     */
    public function saveSatusehatSettings(SatusehatSettingsEntity $entity): void;

    /**
     * Mendapatkan daftar kode ICD-10 dengan filter pencarian.
     * 
     * @param Icd10Entity $entity
     * @return array
     */
    public function getIcd10PageData(Icd10Entity $entity): array;

    /**
     * Menyimpan kode ICD-10 baru ke database.
     * 
     * @param Icd10Entity $entity
     * @return void
     */
    public function storeIcd10(Icd10Entity $entity): void;

    /**
     * Menghapus kode ICD-10 berdasarkan ID (Soft Delete).
     * 
     * @param int $id
     * @param string $actorUuid
     * @return void
     */
    public function deleteIcd10(int $id, string $actorUuid): void;

    /**
     * Mendapatkan metadata manajemen database (nama, versi, ukuran).
     * 
     * @return array
     */
    public function getDatabaseManagementData(): array;

    /**
     * Mendapatkan daftar file backup terbaru dari storage.
     * 
     * @return array
     */
    public function getRecentBackupFiles(): array;

    /**
     * Mendapatkan data datatable untuk riwayat backup.
     * 
     * @param \App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity $entity
     * @return array
     */
    public function getBackupHistoryDatatable(DatabaseBackupHistoryTableEntity $entity): array;

    /**
     * Melakukan export database ke file SQL.
     * 
     * @param \App\Modules\Core\Platform\Entities\DatabaseBackupEntity $entity
     * @return array{path: string, filename: string}
     */
    public function exportDatabase(DatabaseBackupEntity $entity): array;

    /**
     * Resolv path file backup untuk diunduh.
     * 
     * @param \App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity $entity
     * @return array{path: string, filename: string}
     */
    public function resolveBackupDownload(DatabaseBackupDownloadEntity $entity): array;

    /**
     * Melakukan restore database dari file SQL.
     * 
     * @param \App\Modules\Core\Platform\Entities\DatabaseRestoreEntity $entity
     * @return void
     */
    public function restoreDatabase(DatabaseRestoreEntity $entity): void;
}

