<?php

use App\Modules\Clinical\Registration\Http\Controllers\WebPatientController;
use App\Modules\Clinical\Registration\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/registrations', [PendaftaranController::class, 'index']);
    Route::post('/registrations', [PendaftaranController::class, 'store']);
    Route::post('/registrations/{uuid}/status', [PendaftaranController::class, 'updateStatus']);
    Route::delete('/registrations/{uuid}', [PendaftaranController::class, 'destroy']);
    Route::get('/patients', [PendaftaranController::class, 'patientList']);
    Route::get('/patients/{uuid}', [PendaftaranController::class, 'patientShow']);
});
