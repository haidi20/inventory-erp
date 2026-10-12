<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PosPurchaseEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PosPurchaseRepositoryInterface
{
    public function list(PosPurchaseEntity $context): LengthAwarePaginator;
    public function save(PosPurchaseEntity $entity): PurchaseRecordEntity;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity;
    public function invoiceNoExists(string $invoiceNo, string $branchUuid, string $companyUuid): bool;
}
