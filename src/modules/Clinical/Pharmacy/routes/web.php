<?php

use App\Modules\Clinical\Pharmacy\Http\Controllers\FarmasiController;
use App\Modules\Clinical\Pharmacy\Http\Controllers\FarmacyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/', [FarmasiController::class, 'index'])->name('index');
        Route::get('/prescriptions', [FarmasiController::class, 'prescriptions'])->name('prescriptions');
        Route::post('/prescriptions', [FarmasiController::class, 'storeResep'])->name('prescriptions.store');
        Route::get('/returns', [FarmasiController::class, 'returns'])->name('returns');
        Route::post('/returns', [FarmasiController::class, 'storeRetur'])->name('returns.store');
        Route::get('/stock/{uuid}', [FarmasiController::class, 'stockDetail'])->name('stock.detail');
    });

    Route::prefix('pharmac')->name('pharmac.')->group(function () {
        Route::get('/', [FarmacyController::class, 'index'])->name('index');
        Route::get('/prescriptions', [FarmacyController::class, 'recipe'])->name('prescriptions');
        Route::get('/transactions', [FarmacyController::class, 'transaction'])->name('transactions');
        Route::get('/accounting', [FarmacyController::class, 'accounting'])->name('accounting');
        Route::get('/inventori', [FarmacyController::class, 'inventory'])->name('inventori');
        Route::get('/stock-opname', [FarmacyController::class, 'stockOpname'])->name('stock-opname');
        Route::get('/stock-opname/create', [FarmacyController::class, 'stockOpnameCreate'])->name('stock-opname.create');
        Route::get('/stock-opname/{uuid}/edit', [FarmacyController::class, 'stockOpnameEdit'])->name('stock-opname.edit');
        Route::get('/stock-adjustments', [FarmacyController::class, 'stockAdjustment'])->name('stock-adjustments');
        Route::get('/stock-adjustments/create', [FarmacyController::class, 'stockAdjustmentCreate'])->name('stock-adjustments.create');
        Route::get('/stock-adjustments/{uuid}/edit', [FarmacyController::class, 'stockAdjustmentEdit'])->name('stock-adjustments.edit');
        Route::get('/stock-mutations', [FarmacyController::class, 'stockMutation'])->name('stock-mutations');
        Route::get('/stock-mutations/create', [FarmacyController::class, 'stockMutationCreate'])->name('stock-mutations.create');
        Route::get('/stock-mutations/{uuid}/edit', [FarmacyController::class, 'stockMutationEdit'])->name('stock-mutations.edit');
        Route::get('/opening-balance', [FarmacyController::class, 'openingBalance'])->name('opening-balance');
        Route::get('/opening-balance/create', [FarmacyController::class, 'openingBalanceCreate'])->name('opening-balance.create');
        Route::get('/opening-balance/{uuid}/edit', [FarmacyController::class, 'openingBalanceEdit'])->name('opening-balance.edit');
        Route::get('/service-deposits', [FarmacyController::class, 'serviceDeposit'])->name('service-deposits');
        Route::get('/return-residues', [FarmacyController::class, 'residueReturn'])->name('return-residues');
        Route::get('/management', [FarmacyController::class, 'management'])->name('management');
        Route::get('/reports', [FarmacyController::class, 'report'])->name('reports');
    });
});
