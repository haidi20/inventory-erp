<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\TreatmentEntity;

/**
 * TreatmentRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data layanan/tindakan medis (Treatment/Service).
 */
interface TreatmentRepositoryInterface
{
    /**
     * Mendapatkan data halaman layanan.
     * 
     * @param TreatmentEntity $entity
     * @return array
     */
    public function getPageData(TreatmentEntity $entity): array;

    /**
     * Mencari layanan berdasarkan UUID.
     * 
     * @param TreatmentEntity $entity
     * @return TreatmentEntity|null
     */
    public function findByUuid(TreatmentEntity $entity): ?TreatmentEntity;

    public function getByCode(TreatmentEntity $entity): ?TreatmentEntity;

    /**
     * Membuat atau memperbarui data layanan.
     * 
     * @param TreatmentEntity $entity
     * @return TreatmentEntity
     */
    public function save(TreatmentEntity $entity): TreatmentEntity;

    /**
     * Menghapus data layanan.
     * 
     * @param TreatmentEntity $entity
     * @return void
     */
    public function delete(TreatmentEntity $entity): void;
}
