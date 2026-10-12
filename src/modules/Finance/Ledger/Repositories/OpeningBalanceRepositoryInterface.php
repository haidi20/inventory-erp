<?php

namespace App\Modules\Finance\Ledger\Repositories;

use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceFilterEntity;

interface OpeningBalanceRepositoryInterface
{
    public function getOpeningBalanceHeader(string $uuid): ?object;
    public function getOpeningBalanceHeaders(OpeningBalanceFilterEntity $filter): array;
    public function createOpeningBalanceHeader(OpeningBalanceEntity $entity): ?object;
    public function updateOpeningBalanceHeader(OpeningBalanceEntity $entity): bool;
    public function findMappingByKey(string $key): ?object;
    public function getOpeningBalances(string $branchUuid): array;
    public function getItemOpeningBalances(string $branchUuid): array;
    public function updateOpeningBalance(string $branchUuid, string $accountCode, float $amount): bool;
}
