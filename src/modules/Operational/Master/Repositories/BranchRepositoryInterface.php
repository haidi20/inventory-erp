<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Models\Branch;

/**
 * BranchRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data master Cabang (Branch).
 */
interface BranchRepositoryInterface
{
    /**
     * Mendapatkan data halaman cabang dengan statistik cabang aktif.
     * 
     * @param BranchEntity $entity
     * @return array
     */
    public function getPageData(BranchEntity $entity): array;

    /**
     * Mencari cabang berdasarkan UUID.
     * 
     * @param string $uuid
     * @return BranchEntity|null
     */
    public function findByUuid(string $uuid): ?BranchEntity;

    /**
     * Menyimpan data cabang baru atau memperbarui data lama.
     * 
     * @param BranchEntity $entity
     * @return BranchEntity
     */
    public function save(BranchEntity $entity): BranchEntity;

    /**
     * Menghapus cabang (Soft Delete).
     * 
     * @param BranchEntity $entity
     * @return void
     */
    public function delete(BranchEntity $entity): void;

    /**
     * Mendapatkan data cabang berdasarkan nama pendek (Short Name).
     * 
     * @param BranchEntity $entity
     * @return BranchEntity|null
     */
    public function getByShortName(BranchEntity $entity): ?BranchEntity;

    /**
     * Memeriksa apakah cabang memiliki gudang terkait.
     * 
     * @param string $uuid
     * @return bool
     */
    public function hasWarehouses(string $uuid): bool;

    /**
     * Mendapatkan daftar cabang untuk kebutuhan ekspor data.
     * 
     * @param BranchEntity $entity
     * @return array<BranchEntity>
     */
    public function listForExport(BranchEntity $entity): array;
}
