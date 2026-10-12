<?php

namespace App\Modules\Analytics\Reports\Http\Controllers;

use App\Modules\Analytics\Reports\Exports\CashFlowExport;
use App\Modules\Finance\Ledger\Services\CashFlowService;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\ReportScopeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * CashFlowController
 * 
 * Handles API requests for the Cash Flow Statement.
 */
class WebCashFlowController extends Controller
{
    public function __construct(
        protected CashFlowService $service,
        protected ReportScopeService $scopeService,
    ) {}

    /**
     * Get the Cash Flow Statement.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $method = $request->get('method', 'indirect');

            if ($method === 'direct') {
                $data = $this->service->generateDirectMethod($entity);
            } else {
                $data = $this->service->generateIndirectMethod($entity);
            }

            return response()->json([
                'success' => true,
                'data' => $data->toArray(),
                'generated_at' => now()->toDateTimeString(),
            ]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Compare Cash Flow between two periods.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compare(Request $request)
    {
        try {
            $currentEntity = $this->resolveEntity($request);
            $compareEntity = $this->resolveEntity($request, 'compare_period_uuid');
            $data = $this->service->generateComparative(
                $currentEntity,
                $compareEntity
            );

            return response()->json([
                'success' => true,
                'data' => $data,
                'generated_at' => now()->toDateTimeString(),
            ]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Export Cash Flow Statement (Excel)
     */
    public function exportExcel(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $method = $request->get('method', 'indirect');

            $entity = $method === 'direct' 
                ? $this->service->generateDirectMethod($entity)
                : $this->service->generateIndirectMethod($entity);

            $data = $entity->toArray();

            return \Maatwebsite\Excel\Facades\Excel::download(
                new CashFlowExport($data),
                "laporan_arus_kas_{$data['period_info']['period_name']}.xlsx"
            );
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Export Cash Flow Statement (PDF)
     */
    public function exportPdf(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $method = $request->get('method', 'indirect');

            $entity = $method === 'direct' 
                ? $this->service->generateDirectMethod($entity)
                : $this->service->generateIndirectMethod($entity);

            $data = $entity->toArray();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.cash_flow', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download("laporan_arus_kas_{$data['period_info']['period_name']}.pdf");
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function resolveEntity(Request $request, ?string $periodKey = null): FinancialReportEntity
    {
        $entity = FinancialReportEntity::fromArray($request->all());
        $user = $request->user();
        $companyUuid = current_company_uuid();

        $periodUuid = $periodKey ? $request->get($periodKey) : $entity->period_uuid;

        return $this->scopeService->resolveFinancialReportEntity(
            $entity,
            (string) $companyUuid,
            $periodUuid,
        );
    }
}
