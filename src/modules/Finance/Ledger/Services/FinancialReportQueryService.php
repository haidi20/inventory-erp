<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Ledger\Repositories\FinancialReportRepository;

class FinancialReportQueryService
{
    public function __construct(
        protected FinancialReportRepository $repository,
        protected AccountSettingRepository $accountSettingRepository
    ) {}

    public function findPeriod(?string $branchUuid, string $periodUuid)
    {
        return $this->accountSettingRepository->findPeriod($branchUuid, $periodUuid);
    }

    public function findPeriodByPeriod(string $branchUuid, string $period)
    {
        return $this->accountSettingRepository->findPeriodByPeriod($branchUuid, $period);
    }

    public function findBranch(string $branchUuid)
    {
        return $this->accountSettingRepository->findBranch($branchUuid);
    }

    public function getIncomeStatement(string $periodUuid, ?string $branchUuid = null)
    {
        return $this->repository->getIncomeStatement($periodUuid, $branchUuid);
    }

    public function getBalanceSheet(string|int $asOfDate, ?string $branchUuid = null)
    {
        return $this->repository->getBalanceSheet($asOfDate, $branchUuid);
    }

    public function getCashFlowIndirect(string $periodUuid, ?string $branchUuid = null)
    {
        return $this->repository->getCashFlowIndirect($periodUuid, $branchUuid);
    }

    public function getEquityStatement(string $periodUuid, ?string $branchUuid = null)
    {
        return $this->repository->getEquityStatement($periodUuid, $branchUuid);
    }

    public function getAdjustedTrialBalance(string $periodUuid, ?string $branchUuid = null)
    {
        return $this->repository->getAdjustedTrialBalance($periodUuid, $branchUuid);
    }

    public function getARAging(?string $branchUuid = null)
    {
        return $this->repository->getARAging($branchUuid);
    }

    public function getAPAging(?string $branchUuid = null)
    {
        return $this->repository->getAPAging($branchUuid);
    }

    public function getInventoryReport(?string $branchUuid = null)
    {
        return $this->repository->getInventoryReport($branchUuid);
    }

    public function getFixedAssetReport(?string $branchUuid = null)
    {
        return $this->repository->getFixedAssetReport($branchUuid);
    }

    public function getTaxReport(string $periodUuid, ?string $branchUuid = null)
    {
        return $this->repository->getTaxReport($periodUuid, $branchUuid);
    }
}
