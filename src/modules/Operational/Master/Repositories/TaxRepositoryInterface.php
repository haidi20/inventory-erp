<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\TaxEntity;

/**
 * TaxRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Pajak (Tax).
 */
interface TaxRepositoryInterface
{
    /**
     * Mendapatkan data halaman pajak dengan filter dan pencarian.
     * 
     * @param TaxEntity $entity
     * @return array
     */
    public function getPageData(TaxEntity $entity): array;

    /**
     * Mencari pajak berdasarkan UUID, difilter per company.
     *
     * @param TaxEntity $entity
     * @return TaxEntity|null
     */
    public function findByUuid(TaxEntity $entity): ?TaxEntity;

    /**
     * Menyimpan atau memperbarui data pajak.
     * 
     * @param TaxEntity $entity
     * @return TaxEntity
     */
    public function save(TaxEntity $entity): TaxEntity;

    /**
     * Menghapus data pajak (Soft Delete).
     * 
     * @param TaxEntity $entity
     * @return void
     */
    public function delete(TaxEntity $entity): void;

    /**
     * Mendapatkan daftar pajak untuk kebutuhan ekspor data.
     * 
     * @param TaxEntity $entity
     * @return array<TaxEntity>
     */
    public function listForExport(TaxEntity $entity): array;
}
