<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupTypeListResultEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use App\Modules\Operational\Master\Entities\LookupValueListResultEntity;

/**
 * LookupRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data referensi (Lookup).
 */
interface LookupRepositoryInterface
{
    /**
     * Mendapatkan daftar semua tipe lookup.
     * 
     * @return array
     */
    public function getTypes(): LookupTypeListResultEntity;

    /**
     * Mendapatkan semua nilai lookup berdasarkan kode tipe.
     * 
     * @param string $typeCode
     * @return array
     */
    public function getValuesByType(LookupTypeEntity $entity): LookupValueListResultEntity;

    /**
     * Membuat atau memperbarui tipe lookup.
     * 
     * @param LookupTypeEntity $entity
     * @return LookupTypeEntity
     */
    public function saveType(LookupTypeEntity $entity): LookupTypeEntity;

    /**
     * Membuat atau memperbarui nilai lookup.
     * 
     * @param LookupValueEntity $entity
     * @return LookupValueEntity
     */
    public function saveValue(LookupValueEntity $entity): LookupValueEntity;

    /**
     * Menghapus nilai lookup.
     * 
     * @param string $uuid
     * @return void
     */
    public function deleteValue(string $uuid): void;

    public function deleteType(string $uuid): void;

    /**
     * Mencari tipe lookup berdasarkan kode.
     * 
     * @param string $typeCode
     * @return LookupType|null
     */
    public function getTypeByCode(LookupTypeEntity $entity): ?LookupTypeEntity;

    /**
     * Mencari nilai lookup berdasarkan UUID.
     * 
     * @param string $uuid
     * @return LookupValue|null
     */
    public function getValueByUuid(LookupValueEntity $entity): ?LookupValueEntity;

    /**
     * Mencari nilai lookup berdasarkan ID tipe dan kode nilai.
     * 
     * @param string $typeId
     * @param string $code
     * @return LookupValue|null
     */
    public function getValueByCode(LookupValueEntity $entity): ?LookupValueEntity;

    /**
     * Mendapatkan data halaman tipe lookup.
     * 
     * @param LookupTypeEntity $entity
     * @return array
     */
    public function getTypesPageData(LookupTypeEntity $entity): LookupTypeListResultEntity;

    /**
     * Mendapatkan data halaman nilai lookup dalam tipe tertentu.
     * 
     * @param LookupValueEntity $entity
     * @return array
     */
    public function getValuesPageData(LookupValueEntity $entity): LookupValueListResultEntity;
}
