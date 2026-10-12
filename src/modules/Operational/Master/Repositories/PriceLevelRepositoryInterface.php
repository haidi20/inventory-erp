<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\PriceLevelEntity;

/**
 * Interface PriceLevelRepositoryInterface
 *
 * Kontrak untuk pengelolaan level harga produk.
 */
interface PriceLevelRepositoryInterface
{
    public function getPageData(PriceLevelEntity $entity): array;
    public function findByUuid(string $uuid, ?string $companyUuid = null): ?PriceLevelEntity;
    public function save(PriceLevelEntity $entity): PriceLevelEntity;
    public function delete(PriceLevelEntity $entity): void;
    public function getActivePriceLevels(?string $companyUuid = null): array;
}
