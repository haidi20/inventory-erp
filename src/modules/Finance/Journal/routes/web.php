<?php

use App\Modules\Finance\Journal\Http\Controllers\WebAccountingController;
use App\Modules\Finance\Journal\Http\Controllers\CashBankController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('accounting')->name('accounting.')->group(function () {
        // Index (menu tab)
        Route::get('/', [WebAccountingController::class, 'showIndex'])->name('index');

        // Periode
        Route::get('/periods', [WebAccountingController::class, 'showPeriods'])->name('periods');
        Route::post('/periods', [WebAccountingController::class, 'storePeriod'])->name('periods.store');
        Route::post('/periods/{uuid}/activate', [WebAccountingController::class, 'activatePeriod'])->name('periods.activate');
        Route::delete('/periods/{uuid}', [WebAccountingController::class, 'destroyPeriod'])->name('periods.destroy');

        // Akun induk
        Route::get('/parent-accounts', [WebAccountingController::class, 'showParentAccounts'])->name('parent-accounts');
        Route::post('/parent-accounts', [WebAccountingController::class, 'storeParentAccount'])->name('parent-accounts.store');
        Route::put('/parent-accounts/{uuid}', [WebAccountingController::class, 'updateParentAccount'])->name('parent-accounts.update');
        Route::delete('/parent-accounts/{uuid}', [WebAccountingController::class, 'destroyParentAccount'])->name('parent-accounts.destroy');

        // Sub akun
        Route::get('/sub-accounts', [WebAccountingController::class, 'showSubAccounts'])->name('sub-accounts');
        Route::post('/sub-accounts', [WebAccountingController::class, 'storeSubAccount'])->name('sub-accounts.store');
        Route::put('/sub-accounts/{uuid}', [WebAccountingController::class, 'updateSubAccount'])->name('sub-accounts.update');
        Route::delete('/sub-accounts/{uuid}', [WebAccountingController::class, 'destroySubAccount'])->name('sub-accounts.destroy');

        // Chart of accounts
        Route::get('/coa', [WebAccountingController::class, 'showChartOfAccounts'])->name('coa');

        // Saldo awal
        Route::get('/opening-balance', [WebAccountingController::class, 'showOpeningBalance'])->name('opening-balance');
        Route::post('/opening-balance', [WebAccountingController::class, 'storeOpeningBalance'])->name('opening-balance.store');

        // Transaksi
        Route::get('/transactions', [WebAccountingController::class, 'showTransactions'])->name('transactions');
        Route::post('/transactions', [WebAccountingController::class, 'storeTransaction'])->name('transactions.store');
        Route::delete('/transactions/{uuid}', [WebAccountingController::class, 'destroyTransaction'])->name('transactions.destroy');

        // Jurnal & laporan
        Route::get('/journals', [WebAccountingController::class, 'showJournals'])->name('journals');
        Route::get('/general-ledger', [WebAccountingController::class, 'showGeneralLedger'])->name('general-ledger');
        Route::get('/trial-balance', [WebAccountingController::class, 'showTrialBalance'])->name('trial-balance');
        Route::get('/trial-balance/export', [WebAccountingController::class, 'exportTrialBalance'])->name('trial-balance.export');
        Route::get('/balance-sheet', [WebAccountingController::class, 'showBalanceSheet'])->name('balance-sheet');
        Route::get('/income-statement', [WebAccountingController::class, 'showIncomeStatement'])->name('income-statement');
        Route::get('/period-adjustments', [WebAccountingController::class, 'showPeriodAdjustments'])->name('period-adjustments');
        Route::get('/balance-check', [WebAccountingController::class, 'showBalanceCheck'])->name('balance-check');

        // Kas & Bank
        Route::get('/cash-bank', [CashBankController::class, 'index'])->name('cash-bank');
    });
});
