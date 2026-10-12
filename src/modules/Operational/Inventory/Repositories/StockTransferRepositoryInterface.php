<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Entities\StockTransferEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferListResultEntity;

interface StockTransferRepositoryInterface
{
    public function getPageData(StockTransferFilterEntity $filter): StockTransferListResultEntity;
    public function getByUuid(string $uuid, string $branchUuid, string $companyUuid): ?StockTransferEntity;
    public function store(StockTransferEntity $entity): ?StockTransferEntity;
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool;
    public function getNextSequence(string $branchUuid, string $companyUuid, string $prefix, string $period): int;
}

