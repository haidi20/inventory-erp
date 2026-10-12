<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;

/**
 * Interface InitialBalanceRepositoryInterface
 *
 * Kontrak untuk pengelolaan saldo awal (item dan person).
 */
interface InitialBalanceRepositoryInterface
{
    // Item Balances
    public function getInitialBalances(array $filter): array;
    public function findByItem(string $itemUuid, string $unitUuid, string $warehouseUuid): mixed;
    public function saveItemBalance(array $data): ?OpeningBalanceEntity;

    // Person Balances
    public function getPersonBalances(array $filter): array;
    public function getPersonSaldo(string $personUuid, string $branchUuid): array;
    public function savePersonBalance(array $data): ?OpeningBalanceEntity;
}
