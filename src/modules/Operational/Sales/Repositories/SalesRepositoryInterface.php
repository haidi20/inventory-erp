<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Models\Sale;

/**
 * Interface SalesRepositoryInterface
 *
 * Kontrak untuk pengelolaan transaksi penjualan (Sales).
 */
interface SalesRepositoryInterface
{
    public function save(array $data): ?Sale;
    public function findAll(array $filter): array;
    public function findByUUID(string $uuid, string $branchUuid, string $companyUuid): ?Sale;
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool;
}
