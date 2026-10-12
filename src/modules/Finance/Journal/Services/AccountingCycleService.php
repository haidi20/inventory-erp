<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Entities\AdjustmentEntity;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Repositories\AccountingCycleRepository;

class AccountingCycleService
{
    public function __construct(
        protected AccountingCycleRepository $repository
    ) {}

    public function createPeriod(string $period)
    {
        return $this->repository->createPeriod($period);
    }

    public function activatePeriod(string $uuid): void
    {
        $this->repository->activatePeriod($uuid);
    }

    public function deletePeriod(string $uuid): void
    {
        $this->repository->deletePeriod($uuid);
    }

    public function periodExistsForCompany(string $uuid, string $companyUuid): bool
    {
        return $this->repository->periodExistsForCompany($uuid, $companyUuid);
    }

    public function generateAdjustingJournals(FinancialReportEntity $entity)
    {
        $this->repository->validatePeriodForAdjustment($entity->period_uuid, $entity->branch_uuid);
        return $this->repository->generateAdjustingJournals($entity->period_uuid, $entity->branch_uuid);
    }

    public function generateAdjustingJournal(AdjustmentEntity $entity)
    {
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generateAccruedExpense(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'accrued_expense';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generateAccruedRevenue(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'accrued_revenue';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generatePrepaidExpense(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'prepaid_expense';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generateUnearnedRevenue(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'unearned_revenue';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generateDepreciation(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'depreciation';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function generateAllowanceForDoubts(AdjustmentEntity $entity)
    {
        $entity->adj_type = 'allowance_doubts';
        return $this->repository->createAdjustingJournal($entity);
    }

    public function closePeriod(FinancialReportEntity $entity)
    {
        return $this->repository->generateClosingJournals($entity->period_uuid, $entity->branch_uuid);
    }

    public function getPostClosingTrialBalance(FinancialReportEntity $entity)
    {
        return $this->repository->getPostClosingTrialBalance($entity->period_uuid, $entity->branch_uuid);
    }

    public function initializeNewPeriod(string $fromPeriodUuid, string $toPeriodUuid, ?string $branchUuid = null)
    {
        return $this->repository->initializeOpeningBalance($fromPeriodUuid, $toPeriodUuid, $branchUuid);
    }

    public function getCashFlow(FinancialReportEntity $entity)
    {
        return $this->repository->getCashFlowIndirect($entity->period_uuid, $entity->branch_uuid);
    }
}

