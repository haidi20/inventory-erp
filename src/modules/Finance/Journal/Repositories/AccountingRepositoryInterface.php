<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\CoaEntity;
use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Entities\AssetEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Coa;
use App\Modules\Finance\Journal\Models\CoaSetup;

/**
 * AccountingRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Akuntansi, COA, dan Aset.
 */
interface AccountingRepositoryInterface
{
    // Account
    public function createAccount(AccountEntity $entity): AccountEntity;
    public function updateAccount(AccountEntity $entity): AccountEntity;
    public function deleteAccount(string $uuid): void;
    public function getAccountByUuid(string $uuid): ?AccountEntity;
    public function getAccountByCode(string $code): ?AccountEntity;
    public function getAllAccounts(): array;

    // COA
    public function createCoa(CoaEntity $entity): CoaEntity;
    public function updateCoa(CoaEntity $entity): CoaEntity;
    public function deleteCoa(string $uuid): void;
    public function getCoaByUuid(string $uuid): ?CoaEntity;
    public function getAllCoas(): array;

    // Asset
    public function createAsset(AssetEntity $entity): AssetEntity;
    public function updateAsset(AssetEntity $entity): AssetEntity;
    public function deleteAsset(string $uuid): void;
    public function getAssetByUuid(string $uuid): ?AssetEntity;
    public function getAssetByCode(string $code): ?AssetEntity;
    public function getAllAssets(AssetEntity $entity): array;

    // Coa Setup
    public function updateCoaSetup(array $data): CoaSetup;
    public function getCoaSetupByProgram(string $program): ?CoaSetup;

    // --- Accounting Cycle ---
    public function syncGeneralLedgerByPeriod(string $periodUuid): void;
    public function generateAdjustingJournals(string $periodUuid, string $branchUuid, bool $autoReverse): string;
    public function generateClosingJournals(string $periodUuid, string $branchUuid): string;
    public function initializeOpeningBalance(string $fromPeriodUuid, string $toPeriodUuid, string $branchUuid): string;
}
