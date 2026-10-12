<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Modules\Operational\Master\Entities\ItemUomEntity;

/**
 * Interface ItemConversionRepositoryInterface
 *
 * Kontrak untuk pengelolaan konversi satuan barang (UOM).
 */
interface ItemConversionRepositoryInterface
{
    public function save(ItemConversionEntity $entity): ?ItemConversionEntity;
    public function delete(ItemConversionEntity $entity): void;
    public function getByUnits(string $fromUnit, string $toUnit, string $branchUuid, string $companyUuid, ?string $itemUuid = null): ?ItemConversionEntity;
    public function getPageData(ItemConversionEntity $entity): array;

    /**
     * Menyimpan data satuan produk (UOM).
     * 
     * @param array $data
     * @return \App\Modules\Operational\Master\Models\ItemUom|null
     */
    public function saveUom(array $data): ?ItemUomEntity;

    /**
     * Mendapatkan daftar satuan berdasarkan produk.
     * 
     * @param string $itemUuid
     * @param string $branchUuid
     * @param string $companyUuid
     * @return array
     */
    public function getUomsByItem(string $itemUuid, string $branchUuid, string $companyUuid): array;

    /**
     * Mencari UOM berdasarkan Legacy ID.
     * 
     * @param int $legacyId
     * @return \App\Modules\Operational\Master\Models\ItemUom|null
     */
    public function getUomByLegacyID(int $legacyId): ?ItemUomEntity;
}
