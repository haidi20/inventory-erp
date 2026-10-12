<?php

use App\Modules\Operational\Sales\Http\Controllers\SaleController;
use App\Modules\Operational\Sales\Http\Controllers\ServiceController;
use App\Modules\Operational\Sales\Http\Controllers\ServicePackageController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\EncounterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:operational.manage'])->group(function () {
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('service-packages', ServicePackageController::class);
    Route::apiResource('encounters', EncounterController::class);
});
