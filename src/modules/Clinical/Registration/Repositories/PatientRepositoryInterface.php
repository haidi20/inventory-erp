<?php

namespace App\Modules\Clinical\Registration\Repositories;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Models\Patient;

/**
 * PatientRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Pasien.
 */
interface PatientRepositoryInterface
{
    /**
     * Mendapatkan data halaman pasien dengan filter dan statistik pendaftaran.
     * 
     * @param PatientEntity $entity
     * @return array
     */
    public function getPageData(PatientEntity $entity): array;
    public function listActiveByCompany(string $companyUuid): array;

    /**
     * Mendaftarkan pasien baru.
     * 
     * @param PatientEntity $entity
     * @return PatientEntity
     */
    public function create(PatientEntity $entity): PatientEntity;

    /**
     * Memperbarui data pasien.
     * 
     * @param PatientEntity $entity
     * @return PatientEntity
     */
    public function update(PatientEntity $entity): PatientEntity;

    /**
     * Mendapatkan data pasien berdasarkan UUID.
     * 
     * @param PatientEntity $entity
     * @return PatientEntity|null
     */
    public function getByUuid(PatientEntity $entity): ?PatientEntity;

    /**
     * Menghapus data pasien (Soft Delete).
     * 
     * @param PatientEntity $entity
     * @return void
     */
    public function save(PatientEntity $entity): PatientEntity;

    public function delete(PatientEntity $entity): void;

    /**
     * Memperbarui informasi SATUSEHAT pasien.
     * 
     * @param string $nik
     * @param string $satusehatId
     * @return void
     */
    public function updateSatusehatInfo(string $nik, string $satusehatId): void;
    public function updateIhsNumberByNik(string $nik, string $ihsNumber): void;
}
