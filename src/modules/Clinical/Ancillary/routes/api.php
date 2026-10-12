<?php

use App\Modules\Clinical\Ancillary\Http\Controllers\LabOrderController;
use App\Modules\Clinical\Ancillary\Http\Controllers\RadiologyOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clinical\Ancillary API Routes
|--------------------------------------------------------------------------
| Endpoint freeze (Section 16):
|   /api/v1/clinic/lab-orders
|   /api/v1/clinic/radiology-orders
*/

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('clinic/lab-orders', LabOrderController::class, [
        'parameters' => ['lab-order' => 'uuid'],
    ]);

    Route::apiResource('clinic/radiology-orders', RadiologyOrderController::class, [
        'parameters' => ['radiology-order' => 'uuid'],
    ]);
});
