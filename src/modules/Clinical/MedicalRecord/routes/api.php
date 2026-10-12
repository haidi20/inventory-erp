<?php

use App\Modules\Clinical\MedicalRecord\Http\Controllers\ComplaintController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\EncounterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('encounters', EncounterController::class);

    Route::get('operational/complaints', [ComplaintController::class, 'index']);
    Route::post('operational/complaints', [ComplaintController::class, 'store']);
    Route::get('operational/complaints/{uuid}', [ComplaintController::class, 'show']);
});
