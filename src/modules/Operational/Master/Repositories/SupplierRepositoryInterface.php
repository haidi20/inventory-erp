<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\SupplierEntity;

/**
 * SupplierRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data pemasok (Supplier).
 */
interface SupplierRepositoryInterface
{
    /**
     * Mendapatkan data halaman pemasok.
     * 
     * @param SupplierEntity $entity
     * @return array
     */
    public function getPageData(SupplierEntity $entity): array;

    /**
     * Mencari pemasok berdasarkan UUID.
     * 
     * @param SupplierEntity $entity
     * @return SupplierEntity|null
     */
    public function findByUuid(SupplierEntity $entity): ?SupplierEntity;

    /**
     * Membuat atau memperbarui data pemasok.
     * 
     * @param SupplierEntity $entity
     * @return SupplierEntity
     */
    public function save(SupplierEntity $entity): SupplierEntity;

    /**
     * Menghapus data pemasok.
     * 
     * @param SupplierEntity $entity
     * @return void
     */
    public function delete(SupplierEntity $entity): void;
}
