<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AccountRepositoryInterface
{
    public function list(AccountEntity $context): LengthAwarePaginator;

    public function save(AccountEntity $entity): Account;

    public function update(AccountEntity $entity): Account;

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Account;

    public function existsInContext(string $uuid, string $branchUuid, string $companyUuid): bool;

    public function codeExists(string $code, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool;

    public function findParentByCode(string $code): ?Account;

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void;
}
