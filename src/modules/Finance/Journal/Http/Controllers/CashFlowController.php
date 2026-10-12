<?php

namespace App\Modules\Finance\Journal\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use App\Modules\Finance\Ledger\Services\CashFlowService;
use App\Shared\Support\ExceptionLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CashFlowController extends Controller
{
    public function __construct(
        protected CashFlowService $service,
        protected AccountingCycleService $accountingCycleService,
        protected BranchService $branchService
    ) {}

    /**
     * Get Indirect Cash Flow Statement.
     */
    public function index(Request $request): JsonResponse
    {
        $periodUuid = $request->query('period_uuid');
        $branchUuid = $request->query('branch_uuid');
        $companyUuid = (string) (current_company_uuid() ?? $request->user()?->active_company_uuid);

        if (!$this->accountingCycleService->periodExistsForCompany((string) $periodUuid, $companyUuid)) {
            return response()->json(['success' => false, 'message' => 'Period not found.'], 404);
        }

        if (!$this->branchService->findByUuidAndCompany((string) $branchUuid, $companyUuid)) {
            return response()->json(['success' => false, 'message' => 'Branch not found.'], 404);
        }

        try {
            $entity = FinancialReportEntity::fromArray([
                'period_uuid' => $periodUuid,
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
                'actor_uuid' => $request->user()?->uuid,
            ]);

            $cashFlow = $this->service->generateIndirectMethod($entity);

            return response()->json([
                'success' => true,
                'data' => $this->formatApiPayload($cashFlow->toArray())
            ]);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Indirect Cash Flow Statement via dedicated route.
     */
    public function getIndirectMethod(Request $request): JsonResponse
    {
        try {
            $periodUuid = $request->query('period_uuid');
            $branchUuid = $request->query('branch_uuid');
            $companyUuid = $request->user()?->company_uuid;

            $entity = FinancialReportEntity::fromArray([
                'period_uuid' => $periodUuid,
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
                'actor_uuid' => $request->user()?->uuid,
            ]);

            $cashFlow = $this->service->generateIndirectMethod($entity);

            return response()->json([
                'success' => true,
                'data' => $this->formatApiPayload($cashFlow->toArray())
            ]);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Direct Cash Flow Statement.
     */
    public function getDirectMethod(Request $request): JsonResponse
    {
        try {
            $periodUuid = $request->query('period_uuid');
            $branchUuid = $request->query('branch_uuid');
            $companyUuid = $request->user()?->company_uuid;

            $entity = FinancialReportEntity::fromArray([
                'period_uuid' => $periodUuid,
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
                'actor_uuid' => $request->user()?->uuid,
            ]);

            $cashFlow = $this->service->generateDirectMethod($entity);

            return response()->json([
                'success' => true,
                'data' => $this->formatApiPayload($cashFlow->toArray())
            ]);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
