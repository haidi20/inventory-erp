<?php

namespace App\Modules\Analytics\Reports\Http\Controllers;

use App\Modules\Analytics\Reports\Exports\BalanceSheetExport;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use App\Modules\Analytics\Reports\Services\ReportScopeService;
use App\Modules\Analytics\Reports\Exports\IncomeStatementExport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * FinancialReportController
 * 
 * Handles general financial reports (Balance Sheet, Income Statement, etc.)
 * Updated to use FinancialReportEntity for standardized data flow.
 */
class FinancialReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $service,
        protected ReportScopeService $scopeService,
    ) {}

    /**
     * Get Income Statement
     */
    public function incomeStatement(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getIncomeStatement($entity);

            return response()->json(['success' => true, 'data' => $data]);
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
     * Get Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getBalanceSheet($entity);

            return response()->json(['success' => true, 'data' => $data]);
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
     * Get Equity Statement
     */
    public function equityStatement(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getEquityStatement($entity);

            return response()->json(['success' => true, 'data' => $data]);
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
     * Export Income Statement (Excel)
     */
    public function exportIncomeStatementExcel(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getIncomeStatement($entity);

            return \Maatwebsite\Excel\Facades\Excel::download(
                new IncomeStatementExport($data),
                "laporan_laba_rugi_{$data['period_info']['period_name']}.xlsx"
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
     * Export Income Statement (PDF)
     */
    public function exportIncomeStatementPdf(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getIncomeStatement($entity);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.income_statement', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download("laporan_laba_rugi_{$data['period_info']['period_name']}.pdf");
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
     * Export Balance Sheet (Excel)
     */
    public function exportBalanceSheetExcel(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getBalanceSheet($entity);

            return \Maatwebsite\Excel\Facades\Excel::download(
                new BalanceSheetExport($data),
                "laporan_neraca_{$data['period_info']['period_name']}.xlsx"
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
     * Export Balance Sheet (PDF)
     */
    public function exportBalanceSheetPdf(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getBalanceSheet($entity);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.balance_sheet', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download("laporan_neraca_{$data['period_info']['period_name']}.pdf");
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
     * Get Adjusted Trial Balance
     */
    public function adjustedTrialBalance(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getAdjustedTrialBalance($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get AR Aging
     */
    public function arAging(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getAccountsReceivableAging($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get AP Aging
     */
    public function apAging(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getAccountsPayableAging($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get Inventory Report
     */
    public function inventoryReport(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getInventoryReport($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get Fixed Asset Report
     */
    public function fixedAssetReport(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getFixedAssetReport($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get Tax Report
     */
    public function taxReport(Request $request)
    {
        try {
            $entity = $this->resolveEntity($request);
            $data = $this->service->getTaxReport($entity);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function resolveEntity(Request $request): FinancialReportEntity
    {
        $entity = FinancialReportEntity::fromArray($request->all());
        $user = $request->user();
        $companyUuid = current_company_uuid();

        return $this->scopeService->resolveFinancialReportEntity(
            $entity,
            (string) $companyUuid,
            $entity->period_uuid,
        );
    }
}
