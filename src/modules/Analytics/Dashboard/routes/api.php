<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Analytics\Dashboard\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Analytics\Dashboard API Routes
|--------------------------------------------------------------------------
| Endpoint freeze (Section 16): /api/v1/dashboard
*/

Route::middleware(['auth:api'])->group(function () {
    Route::get('dashboard', [AnalyticsController::class, 'showOverview'])->name('dashboard.overview');
    Route::get('dashboard/tab-data', [AnalyticsController::class, 'tabData'])->name('dashboard.tab-data');
});
