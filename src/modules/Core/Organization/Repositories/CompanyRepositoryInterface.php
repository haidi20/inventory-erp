<?php

namespace App\Modules\Core\Organization\Repositories;

use App\Modules\Core\Organization\Entities\CompanyEntity;

/**
 * CompanyRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data perusahaan (Company).
 */
interface CompanyRepositoryInterface
{
    /**
     * Mendapatkan data halaman perusahaan.
     * 
     * @param CompanyEntity $entity
     * @return array
     */
    public function getPageData(CompanyEntity $entity): array;

    /**
     * Mencari perusahaan berdasarkan UUID.
     * 
     * @param string $uuid
     * @return CompanyEntity|null
     */
    public function findByUuid(string $uuid): ?CompanyEntity;

    public function findFirstActive(): ?CompanyEntity;

    /**
     * Membuat atau memperbarui data perusahaan.
     * 
     * @param CompanyEntity $entity
     * @return CompanyEntity
     */
    public function save(CompanyEntity $entity): CompanyEntity;

    /**
     * Menghapus data perusahaan.
     * 
     * @param string $uuid
     * @return void
     */
    public function delete(string $uuid): void;
}
