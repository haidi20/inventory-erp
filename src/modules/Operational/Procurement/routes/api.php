<?php

use App\Modules\Operational\Procurement\Http\Controllers\PurchaseController;
use App\Modules\Operational\Procurement\Http\Controllers\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:operational.manage'])->group(function () {
    Route::apiResource('purchases', PurchaseController::class)->only(['index', 'store', 'show']);
    Route::apiResource('operational/purchase-orders', PurchaseOrderController::class, [
        'parameters' => ['purchase-order' => 'uuid'],
    ])->only(['index', 'store', 'show', 'destroy']);
});
