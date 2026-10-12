<?php

use App\Modules\Analytics\Reports\Http\Controllers\ReportPageController;
use App\Modules\Analytics\Reports\Http\Controllers\ReportIndexController;
use App\Modules\Analytics\Reports\Http\Controllers\WebCashFlowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/laporan', [ReportIndexController::class, 'index'])->name('laporan.index');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/cash-flow', [ReportPageController::class, 'showCashFlow'])->name('cash-flow');
        Route::get('/examinations', [ReportPageController::class, 'showExaminationReport'])->name('examinations');
        Route::get('/pos-sales', [ReportPageController::class, 'showPointOfSaleSalesReport'])->name('pos-sales');
        Route::get('/doctor-income', [ReportPageController::class, 'showDoctorIncomeReport'])->name('doctor-income');
        Route::get('/product-vs-service', [ReportPageController::class, 'showProductVsServiceReport'])->name('product-vs-service');
        Route::get('/operational-expenses', [ReportPageController::class, 'showOperationalExpenseReport'])->name('operational-expenses');
    });

    Route::prefix('reports/cash-flow-detail')->name('reports.cash-flow-detail.')->group(function () {
        Route::get('/', [WebCashFlowController::class, 'index'])->name('index');
        Route::get('/compare', [WebCashFlowController::class, 'compare'])->name('compare');
        Route::get('/export/excel', [WebCashFlowController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [WebCashFlowController::class, 'exportPdf'])->name('export.pdf');
    });
});