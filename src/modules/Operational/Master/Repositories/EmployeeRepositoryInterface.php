<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\EmployeeEntity;

/**
 * EmployeeRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Master Karyawan.
 */
interface EmployeeRepositoryInterface
{
    /**
     * Mendapatkan data halaman karyawan.
     * 
     * @param EmployeeEntity $employeeEntity
     * @return array
     */
    public function getPageData(EmployeeEntity $employeeEntity): array;

    /**
     * Menyimpan atau memperbarui data karyawan.
     * 
     * @param EmployeeEntity $employeeEntity
     * @return EmployeeEntity
     */
    public function save(EmployeeEntity $employeeEntity): EmployeeEntity;

    /**
     * Mencari karyawan berdasarkan UUID.
     * 
     * @param EmployeeEntity $entity
     * @return EmployeeEntity|null
     */
    public function findByUuid(EmployeeEntity $entity): ?EmployeeEntity;
    public function updatePractitionerId(string $nik, string $practitionerId): void;

    /**
     * Memeriksa apakah karyawan terhubung dengan akun user.
     * 
     * @param EmployeeEntity $employeeEntity
     * @return bool
     */
    public function hasUser(EmployeeEntity $employeeEntity): bool;

    /**
     * Menghapus karyawan (Soft Delete).
     * 
     * @param EmployeeEntity $entity
     * @return void
     */
    public function delete(EmployeeEntity $entity): void;
}
