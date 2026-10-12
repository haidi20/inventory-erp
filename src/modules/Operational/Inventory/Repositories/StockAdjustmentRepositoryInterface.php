<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Entities\StockAdjustmentEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentListResultEntity;

interface StockAdjustmentRepositoryInterface
{
    public function getAdjustments(StockAdjustmentFilterEntity $filter): StockAdjustmentListResultEntity;
    public function getAdjustmentByUuid(string $uuid, string $branchUuid, string $companyUuid): ?object;
    public function storeAdjustment(StockAdjustmentEntity $entity): ?object;
    public function deleteAdjustment(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool;
}

