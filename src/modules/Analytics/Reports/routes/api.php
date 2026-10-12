<?php

use App\Modules\Analytics\Reports\Http\Controllers\FinancialReportController;
use App\Modules\Analytics\Reports\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::middleware('permission:finance.manage')->group(function () {
        Route::get('reports/income-statement/export/excel', [FinancialReportController::class, 'exportIncomeStatementExcel']);
        Route::get('reports/income-statement/export/pdf', [FinancialReportController::class, 'exportIncomeStatementPdf']);
        Route::get('reports/income-statement', [FinancialReportController::class, 'incomeStatement']);
        Route::get('reports/balance-sheet/export/excel', [FinancialReportController::class, 'exportBalanceSheetExcel']);
        Route::get('reports/balance-sheet/export/pdf', [FinancialReportController::class, 'exportBalanceSheetPdf']);
        Route::get('reports/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
        Route::get('reports/equity', [FinancialReportController::class, 'equityStatement']);
        Route::get('reports/adjusted-trial-balance', [FinancialReportController::class, 'adjustedTrialBalance']);
        Route::get('reports/ar-aging', [FinancialReportController::class, 'arAging']);
        Route::get('reports/ap-aging', [FinancialReportController::class, 'apAging']);
        Route::get('reports/inventory', [FinancialReportController::class, 'inventoryReport']);
        Route::get('reports/fixed-assets', [FinancialReportController::class, 'fixedAssetReport']);
        Route::get('reports/tax', [FinancialReportController::class, 'taxReport']);
    });
    Route::middleware('permission:report.view')->group(function () {
        Route::get('reports/trial-balance/export/excel', [ReportController::class, 'exportTrialBalanceExcel']);
        Route::get('reports/trial-balance/export/pdf', [ReportController::class, 'exportTrialBalancePdf']);
        Route::get('reports/trial-balance', [ReportController::class, 'trialBalance']);
    });
});
