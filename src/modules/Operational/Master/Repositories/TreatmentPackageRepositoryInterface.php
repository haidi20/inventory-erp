<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;

/**
 * TreatmentPackageRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data paket layanan medis (Treatment/Service Package).
 */
interface TreatmentPackageRepositoryInterface
{
    /**
     * Mendapatkan data halaman paket layanan.
     * 
     * @param TreatmentPackageEntity $entity
     * @return array
     */
    public function getPageData(TreatmentPackageEntity $entity): array;

    /**
     * Mencari paket layanan berdasarkan UUID.
     * 
     * @param TreatmentPackageEntity $entity
     * @return TreatmentPackageEntity|null
     */
    public function findByUuid(TreatmentPackageEntity $entity): ?TreatmentPackageEntity;

    public function getByCode(TreatmentPackageEntity $entity): ?TreatmentPackageEntity;

    /**
     * Membuat atau memperbarui data paket layanan beserta item-itemnya.
     * 
     * @param TreatmentPackageEntity $entity
     * @return TreatmentPackageEntity
     */
    public function save(TreatmentPackageEntity $entity): TreatmentPackageEntity;

    /**
     * Menghapus data paket layanan beserta item-itemnya.
     * 
     * @param TreatmentPackageEntity $entity
     * @return void
     */
    public function delete(TreatmentPackageEntity $entity): void;
}
