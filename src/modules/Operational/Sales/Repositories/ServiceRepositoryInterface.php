<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Sales\Entities\ServiceEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceRepositoryInterface
{
    public function list(ServiceEntity $context): LengthAwarePaginator;
    public function save(ServiceEntity $entity): Service;
    public function update(ServiceEntity $entity): Service;
    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Service;
    public function codeExists(string $code, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool;
    public function delete(string $uuid, string $branchUuid, string $companyUuid): void;
}
