<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\MasterCustomerEntity;
use App\Modules\Operational\Master\Models\Person;

/**
 * MasterCustomerRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Master Pelanggan (Customer).
 */
interface MasterCustomerRepositoryInterface
{
    /**
     * Mendapatkan data halaman pelanggan dengan filter dan pencarian.
     * 
     * @param MasterCustomerEntity $entity
     * @return array
     */
    public function getPageData(MasterCustomerEntity $entity): array;

    /**
     * Membuat data pelanggan baru.
     * 
     * @param MasterCustomerEntity $entity
     * @return MasterCustomerEntity
     */
    public function create(MasterCustomerEntity $entity): MasterCustomerEntity;

    /**
     * Memperbarui data pelanggan yang ada.
     * 
     * @param MasterCustomerEntity $entity
     * @return MasterCustomerEntity
     */
    public function update(MasterCustomerEntity $entity): MasterCustomerEntity;

    /**
     * Menghapus data pelanggan (Soft Delete).
     * 
     * @param MasterCustomerEntity $entity
     * @return void
     */
    public function delete(MasterCustomerEntity $entity): void;

    /**
     * Memeriksa apakah pelanggan memiliki transaksi yang terkait.
     * 
     * @param string $uuid
     * @return bool
     */
    public function hasTransactions(string $uuid): bool;
}
