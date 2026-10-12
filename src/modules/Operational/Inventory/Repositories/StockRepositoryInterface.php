<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockReportResultEntity;

interface StockRepositoryInterface
{
    public function getStockBalanceReport(StockFilterEntity $filter): StockReportResultEntity;
    public function saveMovement(StockMovementEntity $entity): StockMovementEntity;
}

