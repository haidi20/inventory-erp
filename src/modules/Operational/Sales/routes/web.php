<?php

use App\Modules\Operational\Sales\Http\Controllers\DashboardController;
use App\Modules\Operational\Sales\Http\Controllers\TransaksiController;
use App\Modules\Operational\Sales\Http\Controllers\ServiceController;
use App\Modules\Operational\Sales\Http\Controllers\ServicePackageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tab-data', [DashboardController::class, 'tabData'])->name('dashboard.tab-data');

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/sales', [TransaksiController::class, 'showSalesList']);
        Route::get('/sales/create', [TransaksiController::class, 'showSalesCreateForm'])->name('sales.create');
        Route::post('/sales', [TransaksiController::class, 'storeSale'])->name('sales.store');
        Route::get('/sales/{saleUuid}', [TransaksiController::class, 'showSaleDetail'])->name('sales.show');
        Route::get('/purchases', [TransaksiController::class, 'showPurchaseList'])->name('purchases');
        Route::get('/examinations', [TransaksiController::class, 'showExaminationList'])->name('examinations');
        Route::post('/examinations', [TransaksiController::class, 'storeExamination'])->name('examinations.store');
        Route::get('/examinations/{registrationUuid}', [TransaksiController::class, 'showExaminationForm']);
        Route::get('/receivables', [TransaksiController::class, 'showReceivableList'])->name('receivables');
        Route::get('/payables', [TransaksiController::class, 'showPayableList'])->name('payables');
    });
});

