<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use App\Modules\Finance\Journal\Repositories\InitialBalanceRepositoryInterface;

/**
 * InitialBalanceService
 *
 * Port dari BE: initial_balance_service.go. Mengelola saldo awal (initial
 * balance) untuk item (stok barang) dan person (customer/supplier).
 */
class InitialBalanceService
{
    public function __construct(
        protected InitialBalanceRepositoryInterface $initialBalanceRepository,
        protected InventoryPublicService $inventoryService
    ) {}

    /** Daftar saldo awal item dengan filter branch/period. */
    public function getItemBalances(FinancialReportEntity $entity): array
    {
        return $this->initialBalanceRepository->getInitialBalances($entity->toArray());
    }

    /** Daftar saldo awal person (customer/supplier) per branch & period. */
    public function getPersonBalances(FinancialReportEntity $entity): array
    {
        return $this->initialBalanceRepository->getPersonBalances($entity->toArray());
    }

    /** Saldo detail per person (semua invoice/aging per orang). */
    public function getPersonSaldos(string $personUuid, string $branchUuid = ''): array
    {
        return $this->initialBalanceRepository->getPersonSaldo($personUuid, $branchUuid);
    }

    public function saveItemBalance(OpeningBalanceEntity $entity): ?OpeningBalanceEntity
    {
        return $this->initialBalanceRepository->saveItemBalance($entity->toArray());
    }

    public function savePersonBalance(OpeningBalanceEntity $entity): ?OpeningBalanceEntity
    {
        return $this->initialBalanceRepository->savePersonBalance($entity->toArray());
    }

    public function deleteItemBalance(string $uuid): void
    {
        if (trim($uuid) === '') {
            throw new \InvalidArgumentException('UUID saldo awal item wajib diisi.');
        }
        $this->inventoryService->deleteItemOpeningBalance($uuid);
    }
}
