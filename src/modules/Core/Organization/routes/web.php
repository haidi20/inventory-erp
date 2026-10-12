<?php

use App\Modules\Core\Organization\Http\Controllers\WebBranchController;
use App\Modules\Core\Organization\Http\Controllers\WebCompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/branches',  [WebBranchController::class,  'index'])->name('branches.index');
        Route::get('/companies', [WebCompanyController::class, 'index'])->name('companies.index');
    });
});
