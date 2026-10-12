<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Finance\Ledger\Entities\CashFlowEntity;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Ledger\Repositories\CashFlowRepository;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;

class CashFlowService
{
    public function __construct(
        protected CashFlowRepository $repository,
        protected GeneralLedgerRepository $ledgerRepository
    ) {}

    public function generateIndirectMethod(FinancialReportEntity $entity): CashFlowEntity
    {
        $period = $this->ledgerRepository->findPeriod($entity->period_uuid);
        if (!$period) {
            throw new \Exception('Periode tidak ditemukan.');
        }

        foreach ($this->ledgerRepository->findPeriodsForCashFlow($period->period, $entity->branch_uuid) as $syncPeriod) {
            $this->ledgerRepository->syncByPeriod($syncPeriod->uuid);
        }

        $branch = $entity->branch_uuid ? $this->ledgerRepository->findBranch($entity->branch_uuid) : null;

        $cashFlow = $this->repository->getIndirectCashFlow($entity->period_uuid, $entity->branch_uuid);
        $cashFlow->periodInfo = [
            'period_name' => $period->period,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
        ];
        $cashFlow->branchInfo = [
            'uuid' => $branch?->uuid,
            'name' => $branch?->name ?: 'Konsolidasi',
        ];

        return $cashFlow;
    }

    public function generateDirectMethod(FinancialReportEntity $entity): CashFlowEntity
    {
        $period = $this->ledgerRepository->findPeriod($entity->period_uuid);
        if (!$period) {
            throw new \Exception('Periode tidak ditemukan.');
        }

        foreach ($this->ledgerRepository->findPeriodsForCashFlow($period->period, $entity->branch_uuid) as $syncPeriod) {
            $this->ledgerRepository->syncByPeriod($syncPeriod->uuid);
        }

        $branch = $entity->branch_uuid ? $this->ledgerRepository->findBranch($entity->branch_uuid) : null;

        $cashFlow = $this->repository->getDirectCashFlow($entity->period_uuid, $entity->branch_uuid);
        $cashFlow->periodInfo = [
            'period_name' => $period->period,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
        ];
        $cashFlow->branchInfo = [
            'uuid' => $branch?->uuid,
            'name' => $branch?->name ?: 'Konsolidasi',
        ];

        return $cashFlow;
    }

    public function generateComparative(FinancialReportEntity $currentEntity, FinancialReportEntity $compareEntity): array
    {
        $current = $this->generateIndirectMethod($currentEntity);
        $previous = $this->generateIndirectMethod($compareEntity);

        return [
            'current' => $current->toArray(),
            'previous' => $previous->toArray(),
        ];
    }
}
