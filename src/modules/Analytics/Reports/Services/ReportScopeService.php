<?php

namespace App\Modules\Analytics\Reports\Services;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use App\Modules\Core\Organization\Services\BranchService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReportScopeService
{
    public function __construct(
        protected AccountingCycleService $accountingCycleService,
        protected BranchService $branchService,
    ) {}

    public function resolveFinancialReportEntity(
        FinancialReportEntity $entity,
        string $companyUuid,
        ?string $periodUuid = null,
    ): FinancialReportEntity {
        if ($periodUuid !== null && $periodUuid !== '') {
            $periodExists = $this->accountingCycleService->periodExistsForCompany($periodUuid, $companyUuid);

            if (!$periodExists) {
                throw new ModelNotFoundException();
            }

            $entity->period_uuid = $periodUuid;
        }

        if ($entity->branch_uuid !== null) {
        $branch = $this->branchService->findByUuidAndCompany($entity->branch_uuid, $companyUuid);

            if (!$branch) {
                throw new ModelNotFoundException();
            }

            $entity->branch_uuid = $branch->uuid;
        }

        $entity->company_uuid = $companyUuid;

        return $entity;
    }

    public function resolvePeriodUuid(string $periodUuid, string $companyUuid): string
    {
        $periodExists = $this->accountingCycleService->periodExistsForCompany($periodUuid, $companyUuid);

        if (!$periodExists) {
            throw new ModelNotFoundException();
        }

        return $periodUuid;
    }

    public function resolveBranchUuid(?string $branchUuid, string $companyUuid): ?string
    {
        if ($branchUuid === null || $branchUuid === '') {
            return null;
        }

        $branch = $this->branchService->findByUuidAndCompany($branchUuid, $companyUuid);

        if (!$branch) {
            throw new ModelNotFoundException();
        }

        return $branch->uuid;
    }
}
