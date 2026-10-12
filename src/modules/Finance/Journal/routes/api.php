<?php

use App\Modules\Finance\Journal\Http\Controllers\AccountController;
use App\Modules\Finance\Journal\Http\Controllers\JournalController;
use App\Modules\Finance\Journal\Http\Controllers\CashFlowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:finance.manage'])->group(function () {
    Route::apiResource('accounts', AccountController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('journals/export/excel', [JournalController::class, 'exportExcel']);
    Route::get('journals/export/pdf', [JournalController::class, 'exportPdf']);
    Route::apiResource('journals', JournalController::class)->only(['index', 'store', 'show']);

    Route::get('accounting/cash-flow/export/excel', [CashFlowController::class, 'exportExcel']);
    Route::get('accounting/cash-flow/export/pdf', [CashFlowController::class, 'exportPdf']);
    Route::get('accounting/cash-flow', [CashFlowController::class, 'index']);
    Route::get('accounting/cash-flow/compare', [CashFlowController::class, 'compare']);
});
