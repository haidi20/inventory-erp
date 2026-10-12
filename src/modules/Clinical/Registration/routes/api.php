<?php

use App\Modules\Clinical\Registration\Http\Controllers\PatientController;
use App\Modules\Clinical\Registration\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:master.manage'])->group(function () {
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('registrations', RegistrationController::class);
});
