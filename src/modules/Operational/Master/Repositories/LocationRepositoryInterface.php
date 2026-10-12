<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Operational\Master\Models\Location;

/**
 * LocationRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Gudang (Location).
 */
interface LocationRepositoryInterface
{
    /**
     * Mendapatkan daftar gudang yang aktif.
     * 
     * @return array<LocationEntity>
     */
    public function getActiveLocations(): array;

    /**
     * Mencari gudang berdasarkan UUID dalam konteks cabang dan perusahaan.
     * 
     * @param LocationEntity $entity
     * @return LocationEntity|null
     */
    public function findByUuid(LocationEntity $entity): ?LocationEntity;

    /**
     * Mendapatkan data halaman gudang dengan filter dan pencarian.
     * 
     * @param LocationEntity $entity
     * @return array
     */
    public function getPageData(LocationEntity $entity): array;

    /**
     * Menyimpan atau memperbarui data gudang.
     * 
     * @param LocationEntity $entity
     * @return LocationEntity
     */
    public function save(LocationEntity $entity): LocationEntity;

    /**
     * Menghapus data gudang (Soft Delete).
     * 
     * @param LocationEntity $entity
     * @return void
     */
    public function delete(LocationEntity $entity): void;

    /**
     * Mencari gudang berdasarkan kode dalam cabang tertentu.
     * 
     * @param LocationEntity $entity
     * @return LocationEntity|null
     */
    public function getByBranchAndCode(LocationEntity $entity): ?LocationEntity;

    /**
     * Mendapatkan daftar gudang untuk kebutuhan ekspor data.
     * 
     * @param LocationEntity $entity
     * @return array<LocationEntity>
     */
    public function listForExport(LocationEntity $entity): array;

    /**
     * Memeriksa apakah gudang memiliki keterkaitan dengan shift.
     * 
     * @param string $uuid
     * @return bool
     */
    public function hasShifts(string $uuid): bool;

    /**
     * Mendapatkan opsi daftar cabang untuk dropdown filter.
     * 
     * @return array
     */
    public function getBranchOptions(?string $companyUuid = null): array;

    /**
     * Mendapatkan cabang berdasarkan short_name (untuk import).
     * 
     * @param string $shortName
     * @return Branch|null
     */
    public function getBranchByShortName(string $shortName): ?array;
}
