<?php

use App\Modules\Finance\Ledger\Http\Controllers\GeneralLedgerController;
use App\Modules\Finance\Ledger\Http\Controllers\FinancialStatementMenuController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:finance.manage'])->group(function () {
    Route::get('general-ledger/export/excel', [GeneralLedgerController::class, 'exportExcel']);
    Route::get('general-ledger/export/pdf', [GeneralLedgerController::class, 'exportPdf']);
    Route::get('general-ledger', [GeneralLedgerController::class, 'index']);
});
