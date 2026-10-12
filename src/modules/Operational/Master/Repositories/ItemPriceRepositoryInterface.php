<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\ProductPriceEntity;

/**
 * Interface ItemPriceRepositoryInterface
 *
 * Kontrak untuk pengelolaan harga barang dan level harga.
 */
interface ItemPriceRepositoryInterface
{
    public function listForExport(array $filter): array;

    public function getActivePriceLevels(?string $companyUuid = null): array;

    public function getByItemCode(string $code): array|object|null;

    public function updateBasePrices(string $itemUuid, float $buyPrice, float $sellPrice, string $actorUuid): bool;

    /**
     * Menyimpan atau mengupdate harga barang.
     */
    public function upsertPrice(ProductPriceEntity $entity): ?ProductPriceEntity;

    /**
     * Mencari harga barang berdasarkan item, unit, dan level harga.
     */
    public function findPrice(string $itemUuid, string $unitUuid, string $priceLevelUuid, string $branchUuid, ?string $companyUuid = null): ?ProductPriceEntity;

    /**
     * Sinkroniasi harga beli ke harga pokok di master item.
     */
    public function updateBasePrice(string $itemUuid, float $price): bool;
}
