<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Database\Eloquent\Builder;

/**
 * UnitRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data satuan (Unit).
 */
interface UnitRepositoryInterface
{
    /**
     * Query builder dasar untuk Unit (untuk kebutuhan read lintas fitur di layer Service).
     */
    public function query(): Builder;

    /**
     * Mendapatkan data halaman unit dengan filter dan pencarian.
     * 
     * @param UnitEntity $entity
     * @return array
     */
    public function getPageData(UnitEntity $entity): array;

    /**
     * Mencari unit berdasarkan UUID.
     * 
     * @param UnitEntity $entity
     * @return UnitEntity|null
     */
    public function findByUuid(UnitEntity $entity): ?UnitEntity;

    /**
     * Mencari unit berdasarkan kode unik.
     * 
     * @param UnitEntity $entity
     * @return UnitEntity|null
     */
    public function getByCode(UnitEntity $entity): ?UnitEntity;

    /**
     * Menyimpan atau memperbarui data unit.
     * 
     * @param UnitEntity $entity
     * @return UnitEntity
     */
    public function save(UnitEntity $entity): UnitEntity;

    /**
     * Menghapus data unit (Soft Delete).
     * 
     * @param UnitEntity $entity
     * @return void
     */
    public function delete(UnitEntity $entity): void;
}
