<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Entities\SaleEntity;
use App\Modules\Operational\Sales\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SaleRepositoryInterface
{
    public function list(SaleEntity $context): LengthAwarePaginator;
    public function save(SaleEntity $entity): Sale;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Sale;
    public function saleNoExists(string $saleNo, string $branchUuid, string $companyUuid): bool;
}
