<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Sales\Entities\ServicePackageEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServicePackageRepositoryInterface
{
    public function list(ServicePackageEntity $context): LengthAwarePaginator;
    public function save(ServicePackageEntity $entity): ServicePackage;
    public function update(ServicePackageEntity $entity): ServicePackage;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?ServicePackage;
    public function codeExists(string $code, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool;
    public function delete(string $uuid, string $branchUuid, string $companyUuid): void;
}
