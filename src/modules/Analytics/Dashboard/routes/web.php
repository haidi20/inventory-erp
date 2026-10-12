<?php

use App\Modules\Analytics\Dashboard\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'showOverview'])->name('index');
    Route::get('/doctors', [AnalyticsController::class, 'showDoctorProfit'])->name('doctors');
    Route::get('/services', [AnalyticsController::class, 'showServiceProfit'])->name('services');
    Route::get('/patients', [AnalyticsController::class, 'showPatientProfit'])->name('patients');
    Route::get('/product-margin', [AnalyticsController::class, 'showProductMargin'])->name('product-margin');
    Route::get('/best-selling-services', [AnalyticsController::class, 'showBestSellingServices'])->name('best-selling-services');
    Route::get('/profitable-patients', [AnalyticsController::class, 'showProfitablePatients'])->name('profitable-patients');
});
