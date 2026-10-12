<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\PersonEntity;
use App\Modules\Operational\Master\Models\Person;

/**
 * PersonRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Subjek (Person/Customer/Supplier).
 */
interface PersonRepositoryInterface
{
    /**
     * Mendapatkan data halaman person dengan filter dan pencarian.
     * 
     * @param PersonEntity $entity
     * @return array
     */
    public function getPageData(PersonEntity $entity): array;

    /**
     * Mencari person berdasarkan UUID.
     * 
     * @param string $uuid
     * @param string $branchUuid
     * @param string $companyUuid
     * @return Person|null
     */
    public function findByUuid(string $uuid, string $branchUuid, string $companyUuid): ?Person;

    /**
     * Menyimpan atau memperbarui data person.
     * 
     * @param PersonEntity $entity
     * @return Person
     */
    public function save(PersonEntity $entity): Person;

    /**
     * Menghapus data person (Soft Delete).
     * 
     * @param PersonEntity $entity
     * @return void
     */
    public function delete(PersonEntity $entity): void;

    /**
     * Mendapatkan daftar person untuk kebutuhan ekspor data.
     * 
     * @param PersonEntity $entity
     * @return array
     */
    public function listForExport(PersonEntity $entity): array;

    /**
     * Mencari person berdasarkan kode unik.
     * 
     * @param string $code
     * @return Person|null
     */
    public function getByCode(string $code): ?Person;
}
