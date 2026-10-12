<?php

namespace App\Modules\Analytics\Reports\Http\Controllers;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Analytics\Reports\Services\TrialBalanceService;
use App\Modules\Analytics\Reports\Services\ReportScopeService;
use App\Modules\Analytics\Reports\Exports\TrialBalanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        protected TrialBalanceService $trialBalanceService,
        protected ReportScopeService $scopeService,
    ) {}

    public function trialBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_uuid' => 'required|uuid',
            'branch_uuid' => 'nullable|uuid',
        ]);

        if ($validator->fails()) {
            ExceptionLogger::log(
                ValidationException::withMessages($validator->errors()->toArray()),
                self::class
            );
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        [$periodUuid, $branchUuid] = $this->resolveScope($request);

        try {
            $data = $this->trialBalanceService->generate($periodUuid, $branchUuid);

            return response()->json([
                'success' => true,
                'data' => $this->formatApiPayload($data),
                'metadata' => [
                    'period_name' => $data->period_info->period_name,
                    'branch_name' => $branchUuid ? 'Cabang' : 'Konsolidasi',
                ]
            ]);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function exportTrialBalanceExcel(Request $request)
    {
        [$periodId, $branchId] = $this->resolveScope($request);

        $tb = $this->trialBalanceService->generate($periodId, $branchId);
        
        return Excel::download(new TrialBalanceExport(collect($tb->accounts)), 'neraca_saldo.xlsx');
    }

    public function exportTrialBalancePdf(Request $request)
    {
        [$periodId, $branchId] = $this->resolveScope($request);

        $tb = $this->trialBalanceService->generate($periodId, $branchId);
        $accounts = $tb->accounts;
        $periodName = $tb->period_info->period_name;

        $pdf = Pdf::loadView('reports.trial_balance_pdf', compact('accounts', 'periodName'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('neraca_saldo.pdf');
    }

    private function resolveScope(Request $request): array
    {
        $user = $request->user();
        $periodUuid = $request->query('period_uuid') ?? $request->query('period_uuid');
        $branchUuid = $request->query('branch_uuid') ?? $request->query('branch_uuid');
        $companyUuid = current_company_uuid();

        return [
            $this->scopeService->resolvePeriodUuid((string) $periodUuid, (string) $companyUuid),
            $this->scopeService->resolveBranchUuid($branchUuid, (string) $companyUuid),
        ];
    }
}
