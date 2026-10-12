<?php

use App\Modules\Finance\Ledger\Http\Controllers\FinancialStatementMenuController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/menu', [FinancialStatementMenuController::class, 'index'])->name('menu');
    });
});
