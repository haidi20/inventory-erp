<?php

use App\Modules\Clinical\MedicalRecord\Http\Controllers\WebComplaintController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\DoctorTaskController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\LabController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\RadiologyController;
use App\Modules\Clinical\MedicalRecord\Http\Controllers\AncillaryWorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/doctor-tasks', [DoctorTaskController::class, 'index'])->name('doctor-tasks');
        Route::get('/ancillary-requests', [AncillaryWorkflowController::class, 'showRequestEntry'])->name('ancillary-requests');
        Route::get('/ancillary-results', [AncillaryWorkflowController::class, 'showResultEntry'])->name('ancillary-results');
    });

    Route::prefix('lab')->name('lab.')->group(function () {
        Route::get('/', [LabController::class, 'index'])->name('index');
        Route::post('/', [LabController::class, 'store'])->name('store');
        Route::get('/{uuid}', [LabController::class, 'show'])->name('show');
        Route::post('/{uuid}/hasil', [LabController::class, 'inputHasil'])->name('hasil');
        Route::post('/{uuid}/status', [LabController::class, 'updateStatus'])->name('status');
        Route::delete('/{uuid}', [LabController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('radiologi')->name('radiologi.')->group(function () {
        Route::get('/', [RadiologyController::class, 'index'])->name('index');
        Route::post('/', [RadiologyController::class, 'store'])->name('store');
        Route::get('/{uuid}', [RadiologyController::class, 'show'])->name('show');
        Route::post('/{uuid}/hasil', [RadiologyController::class, 'inputHasil'])->name('hasil');
        Route::post('/{uuid}/status', [RadiologyController::class, 'updateStatus'])->name('status');
        Route::delete('/{uuid}', [RadiologyController::class, 'destroy'])->name('destroy');
    });

    Route::get('/operational/complaints', [WebComplaintController::class, 'index'])->name('operational.complaints.index');
    Route::post('/operational/complaints', [WebComplaintController::class, 'store'])->name('operational.complaints.store');
    Route::put('/operational/complaints/{uuid}', [WebComplaintController::class, 'update'])->name('operational.complaints.update');
    Route::delete('/operational/complaints/{uuid}', [WebComplaintController::class, 'destroy'])->name('operational.complaints.destroy');
});
