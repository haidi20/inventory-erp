<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Ledger\Repositories\TrialBalanceRepository;

class TrialBalanceQueryService
{
    public function __construct(
        protected TrialBalanceRepository $repository,
        protected AccountSettingRepository $accountSettingRepository
    ) {}

    public function findPeriodById(string $uuid)
    {
        return $this->accountSettingRepository->findPeriodById($uuid);
    }

    public function getBalancesViaStoredProcedure(string $startDate, string $endDate, ?string $branchUuid = null): array
    {
        return $this->repository->getBalancesViaStoredProcedure($startDate, $endDate, $branchUuid);
    }

    public function getBalancesByAccount(string $periodUuid, ?string $branchUuid = null): array
    {
        return $this->repository->getBalancesByAccount($periodUuid, $branchUuid);
    }
}
